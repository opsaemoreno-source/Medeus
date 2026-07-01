<?php

namespace App\Http\Controllers;

use App\Models\ChatbotAiLog;
use Illuminate\Http\Request;

class ChatbotExecutionController extends Controller
{
    public function show($conversationId)
    {
        $logs = ChatbotAiLog::query()
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($log) {
               return $log->message_id ?? $log->id;
            });

        return view('chatbot.execution.show', [
            'conversationId' => $conversationId,
            'logsByMessage' => $logs
        ]);
    }
}
