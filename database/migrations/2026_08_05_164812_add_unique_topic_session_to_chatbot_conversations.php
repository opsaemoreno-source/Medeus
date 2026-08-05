<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corre esta migración SOLO después de verificar que no hay duplicados.
 * El chequeo está automatizado abajo: si encuentra pares repetidos, la
 * migración se detiene con un mensaje en lugar de fallar con un error de
 * constraint a medio camino.
 *
 * Sin esta restricción, el patrón SELECT-then-INSERT del backend puede
 * crear dos conversaciones para el mismo par (topic_id, session_id) bajo
 * una condición de carrera (doble clic, doble carga del iframe).
 */
return new class extends Migration
{
    private const TABLE = 'chatbot_conversations';

    public function up(): void
    {
        $duplicates = DB::table(self::TABLE)
            ->select('topic_id', 'session_id', DB::raw('COUNT(*) AS n'))
            ->groupBy('topic_id', 'session_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Hay ' . $duplicates->count() . ' par(es) (topic_id, session_id) duplicados '
                . 'en ' . self::TABLE . '. Consolidalos antes de aplicar el índice único. '
                . 'Primer caso: topic_id=' . $duplicates->first()->topic_id
                . ', session_id=' . $duplicates->first()->session_id
            );
        }

        // El índice no único que creó la migración original.
        $legacyIndex = 'chatbot_conversations_topic_id_session_id_index';

        if (Schema::hasIndex(self::TABLE, $legacyIndex)) {
            Schema::table(self::TABLE, function (Blueprint $table) use ($legacyIndex) {
                $table->dropIndex($legacyIndex);
            });
        }

        if (! Schema::hasIndex(self::TABLE, 'cc_topic_session_unique')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->unique(
                    ['topic_id', 'session_id'],
                    'cc_topic_session_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            if (Schema::hasIndex(self::TABLE, 'cc_topic_session_unique')) {
                $table->dropUnique('cc_topic_session_unique');
            }

            $table->index(['topic_id', 'session_id']);
        });
    }
};