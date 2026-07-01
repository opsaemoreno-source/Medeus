@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Execution Logs - Conversation #{{ $conversation->id }}</h3>
    @foreach($logsByMessage as $messageId => $logs)
    @php
        $collapseId = 'message_' . $messageId;
        $result = $queryResults[$messageId] ?? null;
    @endphp
    <div class="card mb-3">
        {{-- HEADER DEL MENSAJE (AISLADO) --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Message ID: {{ $messageId }}</strong>
                <span class="text-muted">({{ $logs->count() }} logs)</span>
            </div>
            <button class="btn btn-sm btn-outline-primary"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false">
                Toggle logs
            </button>
        </div>
        {{-- CUERPO COLAPSABLE POR MENSAJE --}}
        <div id="{{ $collapseId }}" class="collapse">
            <div class="card-body">
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
                {{-- QUERY RESULT POR MENSAJE --}}
                @if($result)
                    <div class="mt-3 border-top pt-3">
                        <h6>SQL</h6>
                        <pre class="bg-dark text-white p-2">{!! $result->sql_query !!}</pre>
                        <h6>RESULT JSON</h6>
                        <pre class="bg-dark text-white p-2">{!! $result->result_json !!}</pre>
                    </div>
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