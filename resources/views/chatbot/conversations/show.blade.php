@extends('layouts.app')
@section('content')

<div class="container mt-4">
    {{-- HEADER --}}
    <div class="mb-3">
        <h3>
            Conversación
        </h3>
        <div class="text-muted">
            Tema:
            <span class="badge bg-info text-dark">
                {{ $conversation->topic->name }}
            </span>
            |
            Session ID:
            <code>
                {{ $conversation->session_id }}
            </code>
        </div>
    </div>
    {{-- CHAT CONTAINER --}}
    <div class="card shadow-sm">
        <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
            @foreach($conversation->messages as $message)
                @if($message->role === 'user')
                    {{-- USER MESSAGE --}}
                    <div class="d-flex justify-content-end mb-3">
                        <div class="p-3 bg-primary text-white rounded"
                             style="max-width: 75%;">
                            {!! nl2br(e($message->content)) !!}
                            <div class="small mt-1 opacity-75">
                                {{ $message->created_at->format('d/m/Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                @else
                    {{-- ASSISTANT MESSAGE --}}
                    <div class="d-flex justify-content-start mb-3">
                        <div class="p-3 bg-light border rounded"
                             style="max-width: 75%;">
                            {!! nl2br($message->content) !!}
                            {{-- STATUS BADGE --}}
                            <div class="mt-2">
                                <span class="badge
                                    {{ $message->message_status === 'success' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $message->message_status }}
                                </span>
                                @if($message->error_type)
                                    <span class="badge bg-warning text-dark">
                                        {{ $message->error_type }}
                                    </span>
                                @endif
                            </div>
                            {{-- SQL --}}
                            @if($message->sql_query)
                                <details class="mt-2">
                                    <summary>SQL ejecutado</summary>
                                    <pre class="bg-dark text-white p-2 mt-2 rounded"
                                         style="font-size: 12px;">
{{ $message->sql_query }}
                                    </pre>
                                </details>
                            @endif
                            <div class="small mt-1 text-muted">
                                {{ $message->created_at }}
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection