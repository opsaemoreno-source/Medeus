<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'topic_id',
        'session_id',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ChatbotTopic::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            ChatbotMessage::class,
            'conversation_id'
        );
    }
}