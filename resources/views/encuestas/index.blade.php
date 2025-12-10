@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Encuestas Typeform</h2>

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Botón independiente (fuera de la tabla) --}}
    <div class="mb-3">
        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#procesarModal"
        >
            Procesar encuesta (ingresar ID)
        </button>
    </div>

    {{-- Tabla de encuestas (sin columna "Acciones") --}}
    <div class="card shadow">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Publicación</th>
                        <th>N° Campos</th>
                        <th>N° Respuestas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($encuestas as $e)
                        <tr>
                            <td>{{ $e['id'] }}</td>
                            <td>{{ $e['titulo'] }}</td>
                            <td>{{ is_object($e['fechaCreacion']) ? optional($e['fechaCreacion'])->format('Y-m-d H:i:s') : $e['fechaCreacion'] }}</td>
                            <td>{{ is_object($e['fechaPublicacion']) ? optional($e['fechaPublicacion'])->format('Y-m-d H:i:s') : $e['fechaPublicacion'] }}</td>
                            <td>{{ $e['noCampos'] }}</td>
                            <td>{{ $e['noRespuestas'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No hay registros disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: aquí el usuario escribe el ID manualmente --}}
<div class="modal fade" id="procesarModal" tabindex="-1" aria-labelledby="procesarModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('encuestas.procesar') }}">
        @csrf
        <input type="hidden" name="id" id="idEncuestaInput"> {{-- mantengo hidden para compatibilidad JS/form submit --}}

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="procesarModalLabel">Procesar encuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="idManual" class="form-label">ID de la encuesta</label>
                    <input type="text" class="form-control" id="idManual" placeholder="Ej: pqJpzbQr" required>
                    <div class="form-text">Introduce el ID de la encuesta que deseas procesar.</div>
                </div>
                <div id="procesarModalAlert" class="text-danger small d-none">Por favor introduce un ID válido.</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="procesarSubmit" class="btn btn-primary">Procesar ahora</button>
            </div>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('procesarModal');
    var idManual = document.getElementById('idManual');
    var idHidden = document.getElementById('idEncuestaInput');
    var alertEl = document.getElementById('procesarModalAlert');

    // Antes de enviar el formulario, copia el valor del input visible al hidden
    document.querySelector('#procesarModal form').addEventListener('submit', function (e) {
        var val = idManual.value.trim();
        if (!val) {
            e.preventDefault();
            alertEl.classList.remove('d-none');
            return;
        }
        idHidden.value = val;
        alertEl.classList.add('d-none');
    });

    // Cuando se abra el modal, limpiar campos
    modalEl.addEventListener('show.bs.modal', function () {
        idManual.value = '';
        idHidden.value = '';
        alertEl.classList.add('d-none');
    });
});
</script>
@endsection
