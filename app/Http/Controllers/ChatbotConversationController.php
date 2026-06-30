<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\ChatbotTopic;
use App\Models\ChatbotMessage;
use Illuminate\Http\Request;

class ChatbotConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatbotConversation::query()
            ->with([
                'topic:id,name,slug'
            ])
            ->withCount('messages')
            ->withMin('messages', 'created_at')
            ->withMax('messages', 'created_at');

        // Filtro por tema
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        // Filtro por session_id
        if ($request->filled('session_id')) {
            $query->where('session_id', 'like', '%' . $request->session_id . '%');
        }

        $conversations = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->all());

        $topics = ChatbotTopic::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view(
            'chatbot.conversations.index',
            compact('conversations', 'topics')
        );
    }

    public function show(ChatbotConversation $conversation)
    {
        $conversation->load([
            'topic:id,name,slug',
            'messages' => function ($query) {
                $query->orderBy('created_at');
            }
        ]);

        return view(
            'chatbot.conversations.show',
            compact('conversation')
        );
    }
}