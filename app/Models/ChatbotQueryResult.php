<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotQueryResult extends Model
{
    protected $fillable = [
        'conversation_id',
        'message_id',
        'sql_query',
        'result_json',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatbotConversation::class);
    }

    public function message()
    {
        return $this->belongsTo(ChatbotMessage::class,'message_id');
    }
}
