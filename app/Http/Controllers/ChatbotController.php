<?php

namespace App\Http\Controllers;

use App\Models\ChatbotTopicVersion;
use App\Models\ChatbotTopic;
use App\Services\ChatbotSyncService;
use App\Defaults\ChatbotDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function index()
    {
        $topics = ChatbotTopic::query()
            ->withCount('versions')
            ->orderBy('name')
            ->get();

        return view('chatbot.index', compact('topics'));
    }

    public function create()
    {
        $topic = new ChatbotTopic();

        $topic->config_json = [
            'token' => '',
            'allowed_table' => '',
            'out_of_scope_answer' => '',
        ];

        $topic->analysis_prompt =
            ChatbotDefaults::analysisPrompt();

        $topic->business_context =
            ChatbotDefaults::businessContext();

        $topic->dataset_context =
            ChatbotDefaults::datasetContext();

        $topic->sql_base_prompt =
            ChatbotDefaults::sqlBasePrompt();

        $topic->validation_prompt =
            ChatbotDefaults::validationPrompt();

        return view('chatbot.create', compact('topic'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',

            'config_token' => 'nullable|string|max:255',
            'config_allowed_table' => 'nullable|string|max:255',
            'config_out_of_scope_answer' => 'nullable|string',
        ]);

        $slug = Str::slug($request->name);

        if (ChatbotTopic::where('slug', $slug)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Ya existe un tema con ese nombre.'
                ]);
        }

        $config = [
            'token' => $request->config_token,
            'allowed_table' => $request->config_allowed_table,
            'out_of_scope_answer' => $request->config_out_of_scope_answer,
        ];

        $topic = ChatbotTopic::create([
            'name' => $request->name,
            'slug' => $slug,
            'active' => true,

            'config_json' => $config,

            'analysis_prompt' => $request->analysis_prompt,
            'business_context' => $request->business_context,
            'dataset_context' => $request->dataset_context,
            'sql_base_prompt' => $request->sql_base_prompt,
            'validation_prompt' => $request->validation_prompt,
        ]);

        ChatbotTopicVersion::create([
            'chatbot_topic_id' => $topic->id,

            'config_json' => $topic->config_json,

            'analysis_prompt' => $topic->analysis_prompt,
            'business_context' => $topic->business_context,
            'dataset_context' => $topic->dataset_context,
            'sql_base_prompt' => $topic->sql_base_prompt,
            'validation_prompt' => $topic->validation_prompt,
        ]);

        app(ChatbotSyncService::class)->sync($topic);

        return to_route('chatbot.index', $topic)
            ->with('success', 'Tema creado correctamente.');
    }

    public function edit(ChatbotTopic $topic)
    {
        return view('chatbot.edit', compact('topic'));
    }

    public function update(Request $request, ChatbotTopic $topic)
    {
        $request->validate([
            'name' => 'required|max:255',
            'config_token' => 'nullable|string|max:255',
            'config_allowed_table' => 'nullable|string|max:255',
            'config_out_of_scope_answer' => 'nullable|string',
        ]);

        $config = [
            'token' => $request->config_token,
            'allowed_table' => $request->config_allowed_table,
            'out_of_scope_answer' => $request->config_out_of_scope_answer,
        ];

        $slug = Str::slug($request->name);

        $exists = ChatbotTopic::where('slug', $slug)
            ->where('id', '!=', $topic->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Ya existe un tema con ese nombre.'
                ]);
        }

        $newData = [
            'config_json' => $config,

            'analysis_prompt' => $request->analysis_prompt,
            'business_context' => $request->business_context,
            'dataset_context' => $request->dataset_context,
            'sql_base_prompt' => $request->sql_base_prompt,
            'validation_prompt' => $request->validation_prompt,
        ];

        if ($this->hasChanges($topic, $newData)) {
            ChatbotTopicVersion::create([
                'chatbot_topic_id' => $topic->id,

                'config_json' => $topic->config_json,

                'analysis_prompt' => $topic->analysis_prompt,
                'business_context' => $topic->business_context,
                'dataset_context' => $topic->dataset_context,
                'sql_base_prompt' => $topic->sql_base_prompt,
                'validation_prompt' => $topic->validation_prompt,
            ]);
        }

        $topic->update([
            'name' => $request->name,
            'slug' => $slug,

            'config_json' => $config,

            'analysis_prompt' => $request->analysis_prompt,
            'business_context' => $request->business_context,
            'dataset_context' => $request->dataset_context,
            'sql_base_prompt' => $request->sql_base_prompt,
            'validation_prompt' => $request->validation_prompt,
        ]);

        app(ChatbotSyncService::class)->sync($topic);

        return to_route('chatbot.index', $topic)
            ->with('success', 'Tema actualizado correctamente.');
    }

    public function duplicate(ChatbotTopic $topic)
    {
        $copy = $topic->replicate();

        $copy->name = $topic->name . ' Copia';
        $copy->slug = Str::slug($copy->name);

        $counter = 1;

        while (
            ChatbotTopic::where('slug', $copy->slug)->exists()
        ) {
            $counter++;

            $copy->slug = Str::slug(
                $topic->name . '-copia-' . $counter
            );
        }

        $copy->save();

        app(ChatbotSyncService::class)->sync($copy);

        ChatbotTopicVersion::create([
            'chatbot_topic_id' => $copy->id,

            'config_json' => $copy->config_json,

            'analysis_prompt' => $copy->analysis_prompt,
            'business_context' => $copy->business_context,
            'dataset_context' => $copy->dataset_context,
            'sql_base_prompt' => $copy->sql_base_prompt,
            'validation_prompt' => $copy->validation_prompt,
        ]);

        return to_route('chatbot.edit', $copy)
            ->with('success', 'Tema duplicado correctamente.');
    }

    public function deactivate(ChatbotTopic $topic)
    {
        $topic->update([
            'active' => false,
            'sync_status' => 'disabled'
        ]);

        app(ChatbotSyncService::class)->deactivate($topic);

        return back();
    }

    public function activate(ChatbotTopic $topic)
    {
        $topic->update([
            'active' => true,
            'sync_status' => 'synced'
        ]);

        app(ChatbotSyncService::class)->sync($topic);

        return back();
    }

    public function versions(ChatbotTopic $topic)
    {
        $versions = $topic->versions()
            ->latest()
            ->get();

        return view(
            'chatbot.versions',
            compact('topic', 'versions')
        );
    }

    public function restoreVersion(
        ChatbotTopic $topic,
        ChatbotTopicVersion $version
    )
    {
        if ($version->chatbot_topic_id !== $topic->id) {
            abort(404);
        }
        $topic->update([
            'config_json' => $version->config_json,

            'analysis_prompt' => $version->analysis_prompt,
            'business_context' => $version->business_context,
            'dataset_context' => $version->dataset_context,
            'sql_base_prompt' => $version->sql_base_prompt,
            'validation_prompt' => $version->validation_prompt,
        ]);

        return to_route('chatbot.edit', $topic)
            ->with(
                'success',
                'Versión restaurada correctamente.'
            );
    }

    private function hasChanges(
        ChatbotTopic $topic,
        array $newData
    ): bool {

        return
            $topic->config_json != $newData['config_json'] ||
            $topic->analysis_prompt != $newData['analysis_prompt'] ||
            $topic->business_context != $newData['business_context'] ||
            $topic->dataset_context != $newData['dataset_context'] ||
            $topic->sql_base_prompt != $newData['sql_base_prompt'] ||
            $topic->validation_prompt != $newData['validation_prompt'];
    }
}