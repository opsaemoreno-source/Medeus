@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h3 class="mb-3">
        Ejecución IA - Conversación #{{ $conversation->id }}
    </h3>

    <p class="text-muted">
        Topic: {{ $conversation->topic->name ?? 'Sin topic' }}
    </p>

    @foreach($logs as $log)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <strong>
                    Mensaje #{{ $log->message->id }}
                </strong>

                <span class="badge bg-secondary">
                    {{ $message->role }}
                </span>
            </div>

            <div class="card-body">

                {{-- USER --}}
                <h6>Entrada</h6>
                <div class="p-2 bg-light rounded mb-3">
                    {{ $message->content }}
                </div>

                {{-- RESPONSE --}}
                @if($message->role === 'assistant')
                    <h6>Respuesta</h6>
                    <div class="p-2 bg-light rounded mb-3">
                        {{ $message->content }}
                    </div>
                @endif

                {{-- QUERY RESULT --}}
                @if($message->queryResult)
                    <h6>SQL ejecutado</h6>
                    <pre class="bg-dark text-white p-2 rounded">
{{ $message->queryResult->sql_query }}
                    </pre>

                    <h6>Resultado</h6>
                    <pre class="bg-dark text-white p-2 rounded">
{{ $message->queryResult->result_json }}
                    </pre>
                @endif

                {{-- AI LOGS --}}
                @if($message->aiLogs->count())
                    <hr>
                    <h5>Logs IA</h5>

                    @foreach($message->aiLogs as $log)
                        <div class="border rounded p-2 mb-3">

                            <div class="d-flex justify-content-between">
                                <strong>{{ strtoupper($log->stage) }}</strong>

                                @if($log->success)
                                    <span class="text-success">✔ OK</span>
                                @else
                                    <span class="text-danger">✗ ERROR</span>
                                @endif
                            </div>

                            <small class="text-muted">
                                {{ $log->created_at }}
                            </small>

                            <h6 class="mt-2">Prompt</h6>
                            <pre class="bg-light p-2">{{ $log->prompt }}</pre>

                            <h6>Response</h6>
                            <pre class="bg-light p-2">{{ $log->response }}</pre>

                            @if($log->error_type)
                                <div class="alert alert-danger mt-2">
                                    {{ $log->error_type }}
                                </div>
                            @endif

                        </div>
                    @endforeach
                @endif

            </div>
        </div>
    @endforeach

</div>
@endsection