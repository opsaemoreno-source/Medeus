<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotQueryResult extends Model
{
    protected $table = 'chatbot_query_results';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'sql_query',
        'result_json',
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