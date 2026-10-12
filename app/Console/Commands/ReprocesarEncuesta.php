<?php

namespace App\Console\Commands;

use App\Services\EncuestaProcessorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReprocesarEncuesta extends Command
{
    protected $signature = 'encuestas:reprocesar
        {formId* : ID(s) del formulario de Typeform}
        {--force : No pedir confirmación}';

    protected $description = 'Borra el detalle de una encuesta en BigQuery y lo vuelve a cargar completo desde Typeform';

    public function handle(EncuestaProcessorService $service): int
    {
        $formIds = $this->argument('formId');

        if (!$this->option('force') && !$this->confirm(
            'Se borrará y recargará el detalle de: '.implode(', ', $formIds).'. ¿Continuar?'
        )) {
            return Command::FAILURE;
        }

        $fail = 0;

        foreach ($formIds as $formId) {
            try {
                $resultado = $service->reprocesarEncuesta($formId);

                $this->info("✔ {$formId}: {$resultado['respuestas']} respuestas, {$resultado['filasInsertadas']} filas");

            } catch (\Exception $e) {
                $fail++;

                Log::error("Error al reprocesar encuesta {$formId}", ['error' => $e->getMessage()]);

                $this->error("✖ {$formId}: {$e->getMessage()}");
            }
        }

        return $fail > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
