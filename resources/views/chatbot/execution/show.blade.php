@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Execution Logs - Conversation #{{ $conversation->id }}</h3>
    @foreach($logsByMessage as $messageId => $logs)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Message ID:</strong> {{ $messageId }}
                <span class="text-muted">({{ $logs->count() }})</span>
            </div>
            <button
                class="btn btn-sm btn-primary"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#msg_{{ $messageId }}"
                aria-expanded="false"
                aria-controls="msg_{{ $messageId }}"
                onclick="bootstrap.Collapse.getOrCreateInstance(document.getElementById('msg_{{ $messageId }}')).toggle()">
                Detalle
            </button>
        </div>
        <div id="msg_{{ $messageId }}" class="collapse">
            <div class="card-body">
                @foreach($logs as $log)
                    <div class="border rounded p-2 mb-3">
                        <strong>{{ $log->step_type }}</strong>
                        <hr>
                        <pre style="white-space: pre-wrap;">{{ $log->prompt }}</pre>
                        <pre style="white-space: pre-wrap;">{!! nl2br($log->response) !!}</pre>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
</div>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection