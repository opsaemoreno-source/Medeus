@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Execution Logs - Conversation #{{ $conversation->id }}</h3>
    @foreach($logsByMessage as $messageId => $logs)
    @php
        $safeMessageId = $messageId ?? ('null_' . $loop->index);
        $collapseId = 'msg_' . $conversation->id . '_' . $safeMessageId;
        $result = $queryResults[$messageId] ?? null;
    @endphp
    <div class="card mb-3">
        {{-- HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Message ID: {{ $messageId ?? 'NULL' }}</strong>
                <span class="text-muted">({{ $logs->count() }} logs)</span>
            </div>
            <button class="btn btn-sm btn-primary"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false">
                Toggle
            </button>
        </div>
        {{-- COLLAPSE REALMENTE AISLADO --}}
        <div id="{{ $collapseId }}" class="collapse">
            <div class="card-body">
                @foreach($logs as $log)
                    <div class="border rounded p-2 mb-3">
                        <strong>{{ strtoupper($log->stage) }}</strong>

                        <hr>

                        <pre>{!! $log->prompt !!}</pre>
                        <pre>{!! $log->response !!}</pre>
                    </div>
                @endforeach

                @if($result)
                    <hr>
                    <pre>{!! $result->sql_query !!}</pre>
                    <pre>{!! $result->result_json !!}</pre>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection