<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use Illuminate\Http\Request;

class ChatbotExecutionController extends Controller
{
    public function show(ChatbotConversation $conversation)
    {
        $conversation->load([
            'topic:id,name',
            'messages' => function ($q) {
                $q->select(
                    'id',
                    'conversation_id',
                    'role',
                    'content',
                    'created_at'
                );
            },
            'messages.aiLogs' => function ($q) {
                $q->select(
                    'id',
                    'message_id',
                    'stage',
                    'success',
                    'error_type',
                    'prompt',
                    'response',
                    'created_at'
                );
            },

            'messages.queryResult'
        ]);
    }
}
