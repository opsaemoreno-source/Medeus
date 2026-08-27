@extends('layouts.app')

@section('content')
<div class="page-heading">
    <h2>Temas Chatbot</h2>
    <a href="{{ route('chatbot.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Tema
    </a>
</div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped w-100" id="topicsTable">
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
                                @if($topic->active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>

                            <td>
                                {{ $topic->synced_at ?? 'Nunca' }}
                            </td>

                            <td>
                                {{ $topic->versions_count }}
                            </td>

                            <td>
                                <div class="d-flex flex-wrap gap-1 action-buttons">
                                    <a href="{{ route('chatbot.edit', $topic) }}" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                    <a href="{{ route('chatbot.versions', $topic) }}" class="btn btn-sm btn-outline-secondary" title="Historial">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <form method="POST" action="{{ route('chatbot.duplicate', $topic) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-info" title="Duplicar">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </form>

                                    @if($topic->active)
                                        <form method="POST" action="{{ route('chatbot.deactivate', $topic) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" title="Desactivar">
                                                <i class="bi bi-pause-circle"></i> Desactivar
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('chatbot.activate', $topic) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Activar">
                                                <i class="bi bi-play-circle"></i> Activar
                                            </button>
                                        </form>
                                    @endif
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary copy-url"
                                        data-url="{{ \App\Defaults\ChatbotDefaults::CHAT_URL . $topic->slug }}"
                                        title="Copiar URL">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                </div>
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
@section('scripts')
<script>
$(function () {
    $('#topicsTable').DataTable({
        order: [],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-MX.json' }
    });
});

document.addEventListener('click', async function (e) {
    const button = e.target.closest('.copy-url');

    if (!button) {
        return;
    }

    const url = button.dataset.url;

    try {
        await navigator.clipboard.writeText(url);

        const original = button.innerHTML;
        button.innerHTML = '✓ Copiada';

        setTimeout(() => {
            button.innerHTML = original;
        }, 1500);
    } catch (err) {
        alert('No fue posible copiar la URL.');
    }
});
</script>
@endsection