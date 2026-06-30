<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\ChatbotTopic;
use App\Models\ChatbotMessage;
use Illuminate\Http\Request;

class ChatbotConversationController extends Controller
{
    public function index()
    {
        $conversations = ChatbotConversation::query()
            ->with([
                'topic:id,name,slug'
            ])
            ->withCount('messages')
            ->withMin('messages', 'created_at')
            ->withMax('messages', 'created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view(
            'chatbot.conversations.index',
            compact('conversations')
        );
    }

    public function show(ChatbotConversation $conversation)
    {
        return view(
            'chatbot.conversations.show',
            compact('conversation')
        );
    }
}