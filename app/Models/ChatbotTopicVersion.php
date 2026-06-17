<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $chatbot_topic_id
 * @property array<array-key, mixed>|null $config_json
 * @property string|null $analysis_prompt
 * @property string|null $business_context
 * @property string|null $dataset_context
 * @property string|null $sql_base_prompt
 * @property string|null $validation_prompt
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereAnalysisPrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereBusinessContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereChatbotTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereConfigJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereDatasetContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereSqlBasePrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopicVersion whereValidationPrompt($value)
 * @mixin \Eloquent
 */
class ChatbotTopicVersion extends Model
{
    protected $fillable = [
        'chatbot_topic_id',
        'config_json',
        'analysis_prompt',
        'business_context',
        'dataset_context',
        'sql_base_prompt',
        'validation_prompt'
    ];

    protected $casts = [
        'config_json' => 'array'
    ];
}