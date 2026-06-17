<?php

namespace App\Services;

use App\Models\ChatbotTopic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class ChatbotSyncService
{
    public function sync(ChatbotTopic $topic): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => env('CHATBOT_API_KEY')
                ])
                ->post(env('CHATBOT_API_URL') . '/admin/sync-topic', [
                    'slug' => $topic->slug,
                    'active' => $topic->active,
                    'config_json' => $topic->config_json,
                    'analysis_prompt' => $topic->analysis_prompt,
                    'business_context' => $topic->business_context,
                    'dataset_context' => $topic->dataset_context,
                    'sql_base_prompt' => $topic->sql_base_prompt,
                    'validation_prompt' => $topic->validation_prompt,
                ]);

            $data = $response->json() ?? [];

            if (!is_array($data)) {
                $data = [];
            }

            $success = $response->successful() && ($data['success'] ?? false);

            $topic->update([
                'sync_status' => $success ? 'synced' : 'error',
                'sync_error' => $success ? null : json_encode($data),
                'synced_at' => Carbon::now(),
            ]);

            return [
                'success' => $response->successful() && ($data['success'] ?? false),
                'response' => $data
            ];

        } catch (\Exception $e) {

            $topic->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
                'synced_at' => Carbon::now(),
            ]);

            return [
                'success' => false,
                'response' => $e->getMessage()
            ];
        }
    }

    public function deactivate(ChatbotTopic $topic): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => env('CHATBOT_API_KEY')
                ])
                ->post(
                    env('CHATBOT_API_URL')
                    . '/admin/deactivate-topic/'
                    . $topic->slug
                );

            $data = $response->json() ?? [];

            $success = $response->successful() && ($data['success'] ?? false);

            $topic->update([
                'sync_status' => $success ? 'disabled' : 'error',
                'sync_error' => $success ? null : json_encode($data),
                'synced_at' => Carbon::now(),
            ]);

            return [
                'success' => $response->successful(),
                'response' => $data
            ];

        } catch (\Exception $e) {

            $topic->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
                'synced_at' => Carbon::now(),
            ]);

            return [
                'success' => false,
                'response' => $e->getMessage()
            ];
        }
    }
}