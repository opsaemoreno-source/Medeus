<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_topic_versions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('chatbot_topic_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->json('config_json')->nullable();

            $table->longText('analysis_prompt')->nullable();
            $table->longText('business_context')->nullable();
            $table->longText('dataset_context')->nullable();
            $table->longText('sql_base_prompt')->nullable();
            $table->longText('validation_prompt')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_topic_versions');
    }
};