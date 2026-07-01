<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\ChatbotAiLog;
use App\Models\ChatbotQueryResult;
use Illuminate\Http\Request;

class ChatbotExecutionController extends Controller
{
    public function show(ChatbotConversation $conversation)
    {
        $logs = ChatbotAiLog::query()
            ->with([
                'message:id,conversation_id,role,content,created_at'
            ])
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get();

        $queryResults = ChatbotQueryResult::query()
            ->where('conversation_id', $conversation->id)
            ->get()
            ->keyBy('message_id');

        return view(
            'chatbot.execution.show',
            [
                'conversation' => $conversation,
                'logs' => $logs,
                'queryResults' => $queryResults,
            ]
        );
    }
}
