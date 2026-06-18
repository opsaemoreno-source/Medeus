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
        Schema::table('chatbot_messages', function (Blueprint $table) {

            $table->boolean('include_in_context')
                ->default(true)
                ->after('sql_query');

            $table->string('message_status', 50)
                ->default('success')
                ->after('include_in_context');

            $table->string('error_type', 100)
                ->nullable()
                ->after('message_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
