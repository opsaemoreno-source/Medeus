@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Execution Logs - Conversation #{{ $conversation->id }}</h3>
    @foreach($logsByMessage as $messageId => $logs)
        <div class="card mb-3">

            {{-- HEADER COLAPSABLE --}}
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>
                    Message ID: {{ $messageId }}
                </strong>
                <button class="btn btn-sm btn-outline-primary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#msg-{{ $messageId }}"
                        aria-expanded="false">
                    Ver logs ({{ $logs->count() }})
                </button>
            </div>

            {{-- COLLAPSIBLE BODY --}}
            <div id="msg-{{ $messageId }}" class="collapse">
                <div class="card-body">
                    @php
                        $result = $queryResults[$messageId] ?? null;
                    @endphp
                    @foreach($logs as $log)
                        <div class="border rounded p-2 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ strtoupper($log->stage) }}</strong>
                                @if($log->success)
                                    <span class="text-success">OK</span>
                                @else
                                    <span class="text-danger">ERROR</span>
                                @endif
                            </div>
                            <hr>
                            <h6>Prompt</h6>
                            <pre class="bg-light p-2">{!! $log->prompt !!}</pre>
                            <h6>Response</h6>
                            <pre class="bg-light p-2">{!! $log->response !!}</pre>
                            @if($log->error_type)
                                <div class="alert alert-danger mt-2">
                                    {{ $log->error_type }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                    {{-- QUERY RESULT --}}
                    @if($result)
                        <hr>
                        <h6>SQL</h6>
                        <pre class="bg-dark text-white p-2">{!! $result->sql_query !!}</pre>
                        <h6>RESULT JSON</h6>
                        <pre class="bg-dark text-white p-2">{!! $result->result_json !!}</pre>
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