@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Temas Chatbot</h2>

        <a href="{{ route('chatbot.create') }}" class="btn btn-primary">
            Nuevo Tema
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Estado</th>
                        <th>Última sincronización</th>
                        <th>Versiones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($topics as $topic)
                        <tr>
                            <td>{{ $topic->name }}</td>
                            <td>{{ $topic->slug }}</td>

                            <td>
                                @if($topic->sync_status == 'synced')
                                    <span class="badge bg-success">Sincronizado</span>
                                @elseif($topic->sync_status == 'error')
                                    <span class="badge bg-danger">Error</span>
                                @elseif($topic->sync_status == 'disabled')
                                    <span class="badge bg-secondary">Desactivado</span>
                                @else
                                    <span class="badge bg-warning">Pendiente</span>
                                @endif
                            </td>

                            <td>
                                {{ $topic->synced_at ?? 'Nunca' }}
                            </td>

                            <td>
                                {{ $topic->versions_count }}
                            </td>

                            <td>
                                <a href="{{ route('chatbot.edit', $topic) }}" class="btn btn-sm btn-primary">Editar</a>
                                <a href="{{ route('chatbot.versions', $topic) }}" class="btn btn-sm btn-secondary">Historial</a>
                                <form
                                    method="POST"
                                    action="{{ route('chatbot.duplicate', $topic) }}"
                                    style="display:inline;">
                                    @csrf
                                    <button class="btn btn-sm btn-info">Duplicar</button>
                                </form>

                                @if($topic->sync_status == 'synced')
                                    <form
                                        method="POST"
                                        action="{{ route('chatbot.deactivate', $topic) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button class="btn btn-sm btn-warning">Desactivar</button>
                                    </form>

                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('chatbot.activate', $topic) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Activar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                No hay temas registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>
@endsection