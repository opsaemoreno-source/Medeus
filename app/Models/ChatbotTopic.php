<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChatbotTopicVersion;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $active
 * @property array<array-key, mixed>|null $config_json
 * @property string|null $analysis_prompt
 * @property string|null $business_context
 * @property string|null $dataset_context
 * @property string|null $sql_base_prompt
 * @property string|null $validation_prompt
 * @property \Illuminate\Support\Carbon|null $synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ChatbotTopicVersion> $versions
 * @property-read int|null $versions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereAnalysisPrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereBusinessContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereConfigJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereDatasetContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereSqlBasePrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotTopic whereValidationPrompt($value)
 * @mixin \Eloquent
 */
class ChatbotTopic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'active',
        'config_json',
        'analysis_prompt',
        'business_context',
        'dataset_context',
        'sql_base_prompt',
        'validation_prompt',
        'synced_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'config_json' => 'array',
        'synced_at' => 'datetime'
    ];

    public function versions()
    {
        return $this->hasMany(
            ChatbotTopicVersion::class
        );
    }
}