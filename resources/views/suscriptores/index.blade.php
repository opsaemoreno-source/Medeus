@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Suscriptores</h2>

    <table class="table table-bordered table-striped" id="suscriptoresTable">
        <thead>
            <tr>
                <th>Usuario ID</th>
                <th>Nombre completo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Suscripción Activa</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@section('scripts')
<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#suscriptoresTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('suscriptores.data') }}',
        columns: [
            { data: 'userid' },
            { data: 'nombre_completo' },
            { data: 'correo' },
            { data: 'telefono' },
            { data: 'suscripcionActiva' },
            { data: 'estado' }
        ],
        order: [[1, 'asc']]
    });
});
</script>
@endsection
