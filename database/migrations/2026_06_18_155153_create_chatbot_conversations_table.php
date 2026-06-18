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
        Schema::create('chatbot_conversations', function (Blueprint $table) {

        $table->id();

        $table->foreignId('topic_id')
            ->constrained('chatbot_topics')
            ->cascadeOnDelete();

        $table->string('session_id', 100);

        $table->timestamps();

        $table->index([
            'topic_id',
            'session_id'
        ]);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};
