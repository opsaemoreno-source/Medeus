<div class="mb-3">
    <label class="form-label">
        Nombre
    </label>

    <input
        type="text"
        name="name"
        class="form-control"
        value="{{ old('name', $topic->name ?? '') }}"
        required>
</div>

<div class="mb-3">
    <label class="form-label">
        Config JSON
    </label>

    <textarea
        name="config_json"
        rows="8"
        class="form-control"
        required>{{ old('config_json', isset($topic) ? json_encode($topic->config_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>

    @error('config_json')
        <div class="text-danger mt-1">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">
        Analysis Prompt
    </label>

    <textarea
        name="analysis_prompt"
        rows="12"
        class="form-control">{{ old('analysis_prompt', $topic->analysis_prompt ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">
        Business Context
    </label>

    <textarea
        name="business_context"
        rows="12"
        class="form-control">{{ old('business_context', $topic->business_context ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">
        Dataset Context
    </label>

    <textarea
        name="dataset_context"
        rows="12"
        class="form-control">{{ old('dataset_context', $topic->dataset_context ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">
        SQL Base Prompt
    </label>

    <textarea
        name="sql_base_prompt"
        rows="12"
        class="form-control">{{ old('sql_base_prompt', $topic->sql_base_prompt ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">
        Validation Prompt
    </label>

    <textarea
        name="validation_prompt"
        rows="12"
        class="form-control">{{ old('validation_prompt', $topic->validation_prompt ?? '') }}</textarea>
</div>