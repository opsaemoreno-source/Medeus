<?php

namespace App\Console\Commands;

use App\Models\ChatbotConversation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Borra las conversaciones que quedaron sin ninguna actividad asociada.
 *
 * ¿De dónde salen conversaciones vacías?
 * El backend en Python usa un patrón SELECT-then-INSERT: primero asegura
 * que exista la fila en chatbot_conversations y después guarda el mensaje.
 * Si el request muere entre esos dos pasos (validación rechazada, timeout
 * de Vertex AI, el iframe de Looker Studio que se carga y nadie escribe,
 * un doble clic), la fila queda huérfana para siempre.
 *
 * IMPORTANTE — por qué el filtro de logs no es opcional:
 * chatbot_ai_logs.conversation_id se creó con ->constrained() SIN
 * cascadeOnDelete (ver 2026_06_19_215022_create_chatbot_ai_logs_table).
 * Intentar borrar una conversación que tiene logs de IA falla con un error
 * de foreign key en MySQL. El whereDoesntHave('aiLogs') es justamente lo
 * que hace que este comando nunca choque contra esa restricción.
 *
 * chatbot_messages, chatbot_query_results y chatbot_charts sí tienen
 * cascadeOnDelete, así que ahí el riesgo es el inverso: borrar la
 * conversación se llevaría los hijos sin avisar. Por eso también los
 * chequeamos antes.
 */
class PurgarConversacionesVacias extends Command
{
    protected $signature = 'chatbot:purgar-conversaciones-vacias
                            {--horas=24 : Antigüedad mínima (en horas) para considerar una conversación abandonada}
                            {--lote=500 : Cuántas conversaciones procesar por iteración}
                            {--dry-run : No borra nada, solo reporta qué se borraría}';

    protected $description = 'Borra conversaciones del chatbot sin mensajes ni logs de IA asociados';

    /**
     * Tope duro de iteraciones. Con --lote=500 son 100 000 conversaciones
     * por corrida, más que suficiente. Existe para que un bug futuro en las
     * condiciones no deje el cron girando indefinidamente.
     */
    private const MAX_ITERACIONES = 200;

    public function handle(): int
    {
        $horas   = max(0, (int) $this->option('horas'));
        $lote    = max(1, (int) $this->option('lote'));
        $dryRun  = (bool) $this->option('dry-run');

        $corte = Carbon::now()->subHours($horas);

        $total = $this->candidatas($corte)->count();

        if ($total === 0) {
            $this->info('No hay conversaciones vacías para borrar.');

            return Command::SUCCESS;
        }

        $this->info("Conversaciones vacías anteriores a {$corte->toDateTimeString()}: {$total}");

        if ($dryRun) {
            $this->muestraEjemplos($corte);
            $this->warn('--dry-run activo: no se borró nada.');

            return Command::SUCCESS;
        }

        $borradas    = 0;
        $iteraciones = 0;

        while ($iteraciones < self::MAX_ITERACIONES) {
            $iteraciones++;

            $ids = $this->candidatas($corte)
                ->orderBy('id')
                ->limit($lote)
                ->pluck('id')
                ->all();

            if ($ids === []) {
                break;
            }

            // Volvemos a aplicar TODAS las condiciones dentro del DELETE en
            // lugar de borrar por ID a ciegas. Entre el SELECT de arriba y
            // esta línea puede haber entrado un mensaje nuevo (el chatbot
            // sigue vivo mientras el cron corre); si eso pasa, la fila ya no
            // cumple la condición y el DELETE simplemente la ignora en vez
            // de destruir una conversación que acaba de empezar.
            $afectadas = $this->candidatas($corte)
                ->whereIn('id', $ids)
                ->delete();

            $borradas += $afectadas;

            // Si ninguna del lote se pudo borrar es que todas dejaron de ser
            // candidatas. Sin este corte el while volvería a pedir el mismo
            // lote para siempre.
            if ($afectadas === 0) {
                break;
            }
        }

        $descartadas = $total - $borradas;

        $this->info("Resumen → Candidatas: {$total}, Borradas: {$borradas}, Descartadas: {$descartadas}");

        Log::info('Purga de conversaciones vacías del chatbot', [
            'candidatas'  => $total,
            'borradas'    => $borradas,
            'descartadas' => $descartadas,
            'corte'       => $corte->toDateTimeString(),
            'horas'       => $horas,
        ]);

        return Command::SUCCESS;
    }

    /**
     * Una conversación es candidata si no tiene NADA colgando de ella y ya
     * es lo bastante vieja como para descartar que el usuario la esté
     * usando en este momento.
     *
     * El filtro de antigüedad es la parte crítica: la fila de conversación
     * se crea ANTES del primer mensaje, así que una conversación creada
     * hace 5 segundos está legítimamente vacía y pertenece a alguien que
     * está escribiendo. Sin este filtro el cron le borraría el chat de
     * abajo de los pies.
     */
    private function candidatas(Carbon $corte): Builder
    {
        return ChatbotConversation::query()
            ->whereDoesntHave('messages')
            ->whereDoesntHave('aiLogs')
            ->whereDoesntHave('queryResults')
            ->where('created_at', '<=', $corte)
            ->where(function (Builder $query) use ($corte) {
                $query->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<=', $corte);
            });
    }

    private function muestraEjemplos(Carbon $corte): void
    {
        $filas = $this->candidatas($corte)
            ->orderBy('id')
            ->limit(15)
            ->get(['id', 'topic_id', 'client_id', 'session_id', 'created_at']);

        $this->table(
            ['ID', 'Topic', 'Client', 'Session', 'Creada'],
            $filas->map(fn ($fila) => [
                $fila->id,
                $fila->topic_id,
                $fila->client_id,
                $fila->session_id,
                optional($fila->created_at)->toDateTimeString(),
            ])->all()
        );
    }
}
