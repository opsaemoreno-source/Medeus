<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_ai_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('conversation_id')
                ->constrained('chatbot_conversations');

            $table->foreignId('message_id')
                ->nullable()
                ->constrained('chatbot_messages');

            $table->string('stage');
            // validation
            // sql_generation
            // analysis

            $table->longText('prompt');

            $table->longText('response')
                ->nullable();

            $table->boolean('success')
                ->default(true);

            $table->string('error_type')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_ai_logs');
    }
};
