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
                $q->orderBy('created_at');
            },
            'messages.aiLogs',
            'messages.queryResult',
        ]);

        return view(
            'chatbot.conversations.execution',
            compact('conversation')
        );
    }
}
