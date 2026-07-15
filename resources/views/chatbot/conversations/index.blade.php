@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Str;
@endphp

<div class="container mt-4">
    <h2 class="mb-4">
        Conversaciones del Chatbot
    </h2>

    {{-- FILTROS --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <select name="topic_id" class="form-control">
                        <option value="">-- Todos los temas --</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}"
                                {{ request('topic_id') == $topic->id ? 'selected' : '' }}>
                                {{ $topic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text"
                           name="session_id"
                           class="form-control"
                           placeholder="Buscar session ID"
                           value="{{ request('session_id') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- TABLA --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>Tema</th>
                    <th>Sesión</th>
                    <th>Mensajes</th>
                    <th>Inicio</th>
                    <th>Última actividad</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($conversations as $conversation)
                    @php
                        $last = $conversation->messages_max_created_at;
                        $first = $conversation->messages_min_created_at;
                    @endphp
                    <tr>
                        {{-- TEMA --}}
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ $conversation->topic->name }}
                            </span>
                        </td>
                        {{-- SESSION --}}
                        <td>
                            <code>
                                {{ Str::limit($conversation->session_id, 24) }}
                            </code>
                        </td>
                        {{-- MENSAJES --}}
                        <td>
                            <span class="badge bg-secondary">
                                {{ $conversation->messages_count }}
                            </span>
                        </td>
                        {{-- INICIO --}}
                        <td>
                            @if($first)
                                <span title="{{ $first }}">
                                    {{ \Carbon\Carbon::parse($first)->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                        {{-- ÚLTIMA ACTIVIDAD --}}
                        <td>
                            @if($last)
                                <span title="{{ $last }}">
                                    {{ \Carbon\Carbon::parse($last)->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                        {{-- ACCIÓN --}}
                        <td>
                            <a href="{{ route('chatbot.conversations.show', $conversation->id) }}" class="btn btn-sm btn-primary">
                                Ver conversación
                            </a>
                            <a href="{{ route('chatbot.execution.show', $conversation->id) }}" class="btn btn-sm btn-dark">
                                Ver ejecución IA
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No existen conversaciones.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-3">
        {{ $conversations->links() }}
    </div>

</div>

@endsection