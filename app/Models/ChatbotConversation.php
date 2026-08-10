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
     * Ojo: la FK de esta tabla NO tiene cascadeOnDelete. Borrar una
     * conversación con logs falla con error de foreign key en MySQL.
     */
    public function aiLogs(): HasMany
    {
        return $this->hasMany(
            ChatbotAiLog::class,
            'conversation_id'
        );
    }

    /**
     * message_id es nullable acá, así que puede haber un resultado de
     * BigQuery colgado de una conversación que no tiene mensajes.
     */
    public function queryResults(): HasMany
    {
        return $this->hasMany(
            ChatbotQueryResult::class,
            'conversation_id'
        );
    }

    /**
     * Conversaciones sin ninguna actividad asociada: las que quedan cuando
     * el request muere entre la creación de la fila y el primer mensaje.
     *
     * No incluye filtro de antigüedad a propósito — eso lo decide quien
     * llama, porque una conversación recién creada también está vacía y no
     * se debe tocar.
     */
    public function scopeWithoutActivity(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('messages')
            ->whereDoesntHave('aiLogs')
            ->whereDoesntHave('queryResults');
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
