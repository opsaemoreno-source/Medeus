@extends('layouts.app')

@section('content')
<div class="page-heading">
    <h2>Execution Logs — Conversación #{{ $conversation->id }}</h2>
</div>

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
                aria-controls="msg_{{ $messageId }}">
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
                        <pre style="white-space: pre-wrap;">{!! $log->response !!}</pre>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
@endsection