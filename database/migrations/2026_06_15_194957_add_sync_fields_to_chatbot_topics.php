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
        Schema::table('chatbot_topics', function (Blueprint $table) {

            $table->text('sync_error')
                ->nullable()
                ->after('sync_status');

            $table->timestamp('last_synced_at')
                ->nullable()
                ->after('sync_error');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_topics', function (Blueprint $table) {
            $table->dropColumn([
                'sync_error',
                'last_synced_at'
            ]);
        });
    }
};
