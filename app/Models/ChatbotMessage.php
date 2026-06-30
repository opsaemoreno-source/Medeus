<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'sql_query',
        'include_in_context',
        'message_status',
        'error_type',
        'created_at',
    ];

    protected $casts = [
        'include_in_context' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            ChatbotConversation::class,
            'conversation_id'
        );
    }

    public function aiLogs()
    {
        return $this->hasMany(ChatbotAiLog::class);
    }

    public function queryResult()
    {
        return $this->hasOne(ChatbotQueryResult::class);
    }
}