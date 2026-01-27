@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Usuarios</h2>
    </div>

    <div class="mb-3">
        <h5>Total de Usuarios: <strong>{{ $totalSuscriptores }}</strong></h5>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-3">Estadística por día</h4>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Fecha inicio</label>
                    <input type="date" id="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Fecha fin</label>
                    <input type="date" id="fecha_fin" class="form-control">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="btnFiltrarEstadistica">
                        Consultar
                    </button>
                </div>
            </div>

            <canvas id="graficaEstadistica" height="120"></canvas>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Fecha inicio:</label>
                <input type="date" id="fechaInicio" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Fecha fin:</label>
                <input type="date" id="fechaFin" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="btnFiltrar" class="btn btn-dark w-100">Aplicar filtro</button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a id="btnExportar" class="btn btn-success w-100">Exportar CSV</a>
            </div>
        </div>
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
    let table = $('#suscriptoresTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('suscriptores.data') }}',
            data: function (d) {
                d.fecha_inicio = $('#fechaInicio').val();
                d.fecha_fin = $('#fechaFin').val();
            }
        },
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

    $('#btnFiltrar').on('click', function() {
        table.ajax.reload();
    });

    $('#btnExportar').on('click', function() {
        const inicio = $('#fechaInicio').val();
        const fin = $('#fechaFin').val();

        window.location =
            `/suscriptores/exportar?fecha_inicio=${inicio}&fecha_fin=${fin}&search=${table.search()}`;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {

    function cargarEstadistica() {
        let inicio = $('#fecha_inicio').val();
        let fin = $('#fecha_fin').val();

        $.ajax({
            url: "{{ route('suscriptores.estadistica') }}",
            data: {
                fecha_inicio: inicio,
                fecha_fin: fin
            },
            success: function(res) {
                actualizarGrafica(res);
            }
        });
    }

    // Gráfica
    let ctx = document.getElementById('graficaEstadistica').getContext('2d');
    let grafica;

    function actualizarGrafica(data) {
        let labels = data.map(item => item.fecha);
        let valores = data.map(item => item.total);

        if (grafica) grafica.destroy();

        grafica = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cuentas creadas',
                    data: valores
                }]
            }
        });
    }

    // Botón
    $('#btnFiltrarEstadistica').click(function() {
        cargarEstadistica();
    });

    // Carga inicial sin filtros
    cargarEstadistica();
});
</script>
@endsection
