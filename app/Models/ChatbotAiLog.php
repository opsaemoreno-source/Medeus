<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotAiLog extends Model
{
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

    public function conversation()
    {
        return $this->belongsTo(ChatbotConversation::class);
    }

    public function message()
    {
        return $this->belongsTo(ChatbotMessage::class);
    }
}
