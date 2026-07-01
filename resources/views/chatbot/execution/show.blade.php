@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Execution Logs - Conversation #{{ $conversation->id }}</h3>
    @foreach($logsByMessage as $messageId => $logs)
        <div class="card mb-4">
            <div class="card-header">
                <strong>Message ID: {{ $messageId }}</strong>
            </div>
            <div class="card-body">
                @php
                    $result = $queryResults[$messageId] ?? null;
                @endphp
                {{-- MESSAGE CONTEXT --}}
                <h6>Logs IA</h6>
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
                        <pre>{{ $log->prompt }}</pre>
                        <h6>Response</h6>
                        <pre>{{ $log->response }}</pre>
                        @if($log->error_type)
                            <div class="alert alert-danger mt-2">
                                {{ $log->error_type }}
                            </div>
                        @endif
                    </div>
                @endforeach
                {{-- QUERY RESULT (solo una vez por mensaje) --}}
                @if($result)
                    <hr>
                    <h6>SQL</h6>
                    <pre>{{ $result->sql_query }}</pre>
                    <h6>RESULT JSON</h6>
                    <pre>{{ $result->result_json }}</pre>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection