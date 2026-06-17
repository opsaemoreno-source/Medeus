<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_topics', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->boolean('active')->default(true);

            $table->json('config_json')->nullable();

            $table->longText('analysis_prompt')->nullable();
            $table->longText('business_context')->nullable();
            $table->longText('dataset_context')->nullable();
            $table->longText('sql_base_prompt')->nullable();
            $table->longText('validation_prompt')->nullable();

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_topics');
    }
};