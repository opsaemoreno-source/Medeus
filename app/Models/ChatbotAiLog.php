<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotAiLog extends Model
{
    protected $table = 'chatbot_ai_logs';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'stage',
        'prompt',
        'response',
        'success',
        'error_type',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            ChatbotConversation::class,
            'conversation_id'
        );
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(
            ChatbotMessage::class,
            'message_id'
        );
    }
}