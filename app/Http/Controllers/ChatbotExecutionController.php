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
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->groupBy('message_id');

        $queryResults = ChatbotQueryResult::query()
            ->where('conversation_id', $conversation->id)
            ->get()
            ->keyBy('message_id');

        return view('chatbot.execution.show', [
            'conversation' => $conversation,
            'logsByMessage' => $logs,
            'queryResults' => $queryResults,
        ]);
    }
}
