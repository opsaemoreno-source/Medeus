<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corre esta migración SOLO despues de verificar que no hay duplicados:
 *
 *   SELECT topic_id, session_id, COUNT(*) AS n
 *   FROM chatbot_conversations
 *   GROUP BY topic_id, session_id
 *   HAVING n > 1;
 *
 * Si devuelve filas, consolidalas antes de continuar. Sin esta restriccion
 * el backend puede crear dos conversaciones para el mismo par por una
 * condicion de carrera entre el SELECT y el INSERT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_conversations', function (Blueprint $table) {
            $table->dropIndex('chatbot_conversations_topic_id_session_id_index');

            $table->unique(
                ['topic_id', 'session_id'],
                'cc_topic_session_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_conversations', function (Blueprint $table) {
            $table->dropUnique('cc_topic_session_unique');
            $table->index(['topic_id', 'session_id']);
        });
    }
};
