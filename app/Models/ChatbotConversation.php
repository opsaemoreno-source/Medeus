<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'topic_id',
        'client_id',
        'session_id',
        'title',
        'hidden_at',
        'last_activity_at',
    ];

    protected $casts = [
        'hidden_at' => 'datetime',
        'last_activity_at' => 'datetime',
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

    /**
     * Conversaciones que el usuario final vació. Siguen existiendo para
     * auditoría, pero él ya no las puede abrir.
     */
    public function scopeHidden(Builder $query): Builder
    {
        return $query->whereNotNull('hidden_at');
    }

    public function scopeVisibleToUser(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }
}
