<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_charts', function (Blueprint $table) {
            $table->id();

            // IMPORTANTE: el tipo de columna aquí debe coincidir EXACTAMENTE
            // con el tipo de "id" en chatbot_conversations y chatbot_messages,
            // o la FK fallará al migrar. foreignId() asume unsignedBigInteger
            // (equivalente a $table->id() en esas tablas). Si esas tablas
            // fueron creadas con $table->increments('id') en vez de
            // $table->id(), cambia esta línea por:
            // $table->unsignedInteger('conversation_id');
            $table->foreignId('conversation_id')
                ->constrained('chatbot_conversations')
                ->cascadeOnDelete();

            // Mismo comentario que arriba, pero contra chatbot_messages.
            $table->foreignId('message_id')
                ->constrained('chatbot_messages')
                ->cascadeOnDelete();

            $table->json('chart_json');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_charts');
    }
};