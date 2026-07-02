<ul class="nav nav-tabs" id="topicTabs" role="tablist">

    <li class="nav-item">
        <button
            type="button"
            class="nav-link active"
            data-bs-toggle="tab"
            data-bs-target="#general">
            General
        </button>
    </li>

    <li class="nav-item">
        <button
            type="button"
            class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#config">
            Configuración
        </button>
    </li>

    <li class="nav-item">
        <button
            type="button"
            class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#prompts">
            Prompts
        </button>
    </li>

    <li class="nav-item">
        <button
            type="button"
            class="nav-link"
            data-bs-toggle="tab"
            data-bs-target="#context">
            Contexto
        </button>
    </li>

</ul>

<div class="tab-content border border-top-0 p-4">
    <div class="tab-pane fade show active" id="general">
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
    </div>

    <div class="tab-pane fade" id="config">
        <div class="mb-3">
            <label class="form-label">
                Config JSON
            </label>

            <div class="mb-3">
                <label class="form-label">
                    Token
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="config_token"
                    value="{{ old('config_token', $topic->config_json['token'] ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Allowed Table
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="config_allowed_table"
                    value="{{ old('config_allowed_table', $topic->config_json['allowed_table'] ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Out Of Scope Answer
                </label>

                <textarea
                    class="form-control"
                    rows="5"
                    name="config_out_of_scope_answer">{{ old('config_out_of_scope_answer', $topic->config_json['out_of_scope_answer'] ?? '') }}</textarea>
            </div>

            @error('config_json')
                <div class="text-danger mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="tab-pane fade" id="prompts">
        <div class="mb-3">
            <label class="form-label">
                Analysis Prompt
            </label>

            <textarea
                name="analysis_prompt"
                style="min-height: 350px;
                font-family: monospace;"
                class="form-control">{{ old('analysis_prompt', $topic->analysis_prompt ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">
                SQL Base Prompt
            </label>

            <textarea
                name="sql_base_prompt"
                style="min-height: 350px;
                font-family: monospace;"
                class="form-control">{{ old('sql_base_prompt', $topic->sql_base_prompt ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Validation Prompt
            </label>

            <textarea
                name="validation_prompt"
                style="min-height: 350px;
                font-family: monospace;"
                class="form-control">{{ old('validation_prompt', $topic->validation_prompt ?? '') }}</textarea>
        </div>
    </div>

    <div class="tab-pane fade" id="context">
        <div class="mb-3">
            <label class="form-label">
                Business Context
            </label>

            <textarea
                name="business_context"
                style="min-height: 350px;
                font-family: monospace;"
                class="form-control">{{ old('business_context', $topic->business_context ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Dataset Context
            </label>

            <textarea
                name="dataset_context"
                style="min-height: 350px;
                font-family: monospace;"
                class="form-control">{{ old('dataset_context', $topic->dataset_context ?? '') }}</textarea>
        </div>
    </div>
</div>