<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Portable entre SQLite (local) y MySQL (producción).
 *
 * Es idempotente a propósito: si una corrida anterior falló a mitad de
 * camino, la fila en `migrations` no se escribió pero las columnas sí
 * quedaron creadas. Los guards permiten volver a correr `migrate` sin
 * tener que limpiar nada a mano.
 */
return new class extends Migration
{
    private const TABLE = 'chatbot_conversations';

    public function up(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {

            // Identificador persistente del navegador/cliente.
            // Un client_id puede tener MUCHOS session_id (uno por hilo de chat).
            if (! Schema::hasColumn(self::TABLE, 'client_id')) {
                $table->string('client_id', 100)
                    ->nullable()
                    ->after('topic_id');
            }

            // Texto derivado del primer mensaje del usuario, para el listado de chats.
            if (! Schema::hasColumn(self::TABLE, 'title')) {
                $table->string('title', 255)
                    ->nullable()
                    ->after('session_id');
            }

            // "Vaciar chat": la fila se conserva para auditoría, pero el usuario
            // final ya no puede leerla ni verla en su listado.
            if (! Schema::hasColumn(self::TABLE, 'hidden_at')) {
                $table->timestamp('hidden_at')
                    ->nullable()
                    ->after('title');
            }

            // Ordenamiento del listado sin depender de updated_at.
            if (! Schema::hasColumn(self::TABLE, 'last_activity_at')) {
                $table->timestamp('last_activity_at')
                    ->nullable()
                    ->after('hidden_at');
            }
        });

        if (! Schema::hasIndex(self::TABLE, 'cc_topic_client_hidden_idx')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->index(
                    ['topic_id', 'client_id', 'hidden_at'],
                    'cc_topic_client_hidden_idx'
                );
            });
        }

        $this->backfill();
    }

    /**
     * Los tres backfills filtran por IS NULL, así que correrlos dos veces
     * no cambia nada.
     */
    private function backfill(): void
    {
        // Las conversaciones existentes se adoptan como si el session_id
        // antiguo fuera también el client_id. Esto hace que el historial
        // actual de cada navegador siga siendo accesible.
        DB::table(self::TABLE)
            ->whereNull('client_id')
            ->update([
                'client_id' => DB::raw('session_id'),
            ]);

        DB::table(self::TABLE)
            ->whereNull('last_activity_at')
            ->update([
                'last_activity_at' => DB::raw('updated_at'),
            ]);

        // Título aproximado para las conversaciones ya existentes.
        //
        // SUBSTR() y la subconsulta correlacionada sin alias de tabla en el
        // UPDATE funcionan igual en SQLite y en MySQL. LEFT() y
        // `UPDATE tabla alias SET alias.col` son sintaxis solo de MySQL.
        DB::statement('
            UPDATE ' . self::TABLE . '
            SET title = (
                SELECT SUBSTR(m.content, 1, 120)
                FROM chatbot_messages m
                WHERE m.conversation_id = ' . self::TABLE . '.id
                  AND m.role = \'user\'
                ORDER BY m.created_at ASC
                LIMIT 1
            )
            WHERE title IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            if (Schema::hasIndex(self::TABLE, 'cc_topic_client_hidden_idx')) {
                $table->dropIndex('cc_topic_client_hidden_idx');
            }
        });

        Schema::table(self::TABLE, function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['client_id', 'title', 'hidden_at', 'last_activity_at'],
                fn ($column) => Schema::hasColumn(self::TABLE, $column)
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};