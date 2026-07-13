<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotChart extends Model
{
    protected $table = 'chatbot_charts';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'chart_json',
    ];

    protected $casts = [
        'chart_json' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }

    public function message()
    {
        return $this->belongsTo(ChatbotMessage::class, 'message_id');
    }
}