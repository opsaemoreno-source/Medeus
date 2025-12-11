@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Suscriptores</h2>
        <!-- Puedes agregar botones aquí, por ejemplo, "Nuevo Suscriptor" si lo necesitas -->
    </div>

    <table class="table table-hover table-bordered table-striped" id="suscriptoresTable">
        <thead class="table-dark">
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
<!-- jQuery y DataTables -->
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
        order: [[1, 'asc']],
        responsive: true,
        language: {
            processing: "Cargando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "No hay registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
        }
    });
});
</script>
@endsection
