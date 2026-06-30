@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <h2 class="mb-4">
        Conversaciones del Chatbot
    </h2>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>Tema</th>
                    <th>Sesión</th>
                    <th>Mensajes</th>
                    <th>Inicio</th>
                    <th>Último mensaje</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($conversations as $conversation)
                    <tr>
                        <td>
                            {{ $conversation->topic->name }}
                        </td>
                        <td>
                            <code>
                                {{ \Illuminate\Support\Str::limit($conversation->session_id, 24) }}
                            </code>
                        </td>
                        <td>
                            {{ $conversation->messages_count }}
                        </td>
                        <td>
                            {{ optional($conversation->messages_min_created_at)?->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            {{ optional($conversation->messages_max_created_at)?->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <a
                                href="{{ route('chatbot.conversations.show', $conversation) }}"
                                class="btn btn-sm btn-primary">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
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