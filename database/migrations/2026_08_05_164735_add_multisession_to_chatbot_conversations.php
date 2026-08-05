<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_conversations', function (Blueprint $table) {

            // Identificador persistente del navegador/cliente.
            // Un client_id puede tener MUCHOS session_id (uno por hilo de chat).
            $table->string('client_id', 100)
                ->nullable()
                ->after('topic_id');

            // Texto derivado del primer mensaje del usuario, para el listado de chats.
            $table->string('title', 255)
                ->nullable()
                ->after('session_id');

            // "Vaciar chat": la fila se conserva para auditoría, pero el usuario
            // final ya no puede leerla ni verla en su listado.
            $table->timestamp('hidden_at')
                ->nullable()
                ->after('title');

            // Ordenamiento del listado sin depender de updated_at.
            $table->timestamp('last_activity_at')
                ->nullable()
                ->after('hidden_at');

            $table->index(
                ['topic_id', 'client_id', 'hidden_at'],
                'cc_topic_client_hidden_idx'
            );
        });

        // Backfill: las conversaciones existentes se adoptan como si el
        // session_id antiguo fuera también el client_id. Esto hace que el
        // historial actual de cada navegador siga siendo accesible.
        DB::table('chatbot_conversations')
            ->whereNull('client_id')
            ->update([
                'client_id' => DB::raw('session_id'),
            ]);

        DB::statement('
            UPDATE chatbot_conversations
            SET last_activity_at = updated_at
            WHERE last_activity_at IS NULL
        ');

        // Título aproximado para las conversaciones ya existentes.
        DB::statement("
            UPDATE chatbot_conversations c
            SET c.title = (
                SELECT LEFT(m.content, 120)
                FROM chatbot_messages m
                WHERE m.conversation_id = c.id
                  AND m.role = 'user'
                ORDER BY m.created_at ASC
                LIMIT 1
            )
            WHERE c.title IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('chatbot_conversations', function (Blueprint $table) {
            $table->dropIndex('cc_topic_client_hidden_idx');
            $table->dropColumn([
                'client_id',
                'title',
                'hidden_at',
                'last_activity_at',
            ]);
        });
    }
};
