@php
    use Carbon\Carbon;

    $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
@endphp
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
                    <input type="date" id="grafica_fecha_inicio" class="form-control" value="{{$fechaInicio}}">
                </div>
                <div class="col-md-3">
                    <label>Fecha fin</label>
                    <input type="date" id="grafica_fecha_fin" class="form-control" value="{{$fechaFin}}">
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
        <h5 class="mb-3">Filtros demográficos</h5>

        <div class="row">
            <div class="col-md-3">
                <label>Fecha inicio</label>
                <input type="date" id="filtro_fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
            </div>

            <div class="col-md-3">
                <label>Fecha fin</label>
                <input type="date" id="filtro_fecha_fin" class="form-control" value="{{ $fechaFin }}">
            </div>

            <div class="col-md-3">
                <label>Género</label>
                <select id="genero" class="form-control">
                    <option value="">Todos</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>País</label>
                <input type="text" id="pais" class="form-control" placeholder="Ej: Honduras">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3">
                <label>Ciudad</label>
                <input type="text" id="ciudad" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Canal</label>
                <select id="canal" class="form-control">
                    <option value="">Todos</option>
                    <option value="web">Web</option>
                    <option value="app">App</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Edad mínima</label>
                <input type="number" id="edad_min" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Edad máxima</label>
                <input type="number" id="edad_max" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Nivel educativo</label>
                <select id="nivelEducativo" class="form-control">
                    <option value="">Todos</option>
                    <!-- Cargar dinámicamente si se desea -->
                </select>
            </div>

            <div class="col-md-3">
                <label>Profesión</label>
                <select id="profesion" class="form-control">
                    <option value="">Todas</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Estado civil</label>
                <select id="estadoCivil" class="form-control">
                    <option value="">Todos</option>
                </select>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-3">
                <button id="btnFiltrar" class="btn btn-dark w-100">
                    Aplicar filtros
                </button>
            </div>

            <div class="col-md-3">
                <button id="btnLimpiar" class="btn btn-outline-secondary w-100">
                    Limpiar
                </button>
            </div>

            <div class="col-md-3">
                <a id="btnExportar" class="btn btn-success w-100">
                    Exportar CSV
                </a>
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
                d.fecha_inicio    = $('#filtro_fecha_inicio').val();
                d.fecha_fin       = $('#filtro_fecha_fin').val();
                d.genero          = $('#genero').val();
                d.pais            = $('#pais').val();
                d.ciudad          = $('#ciudad').val();
                d.canal           = $('#canal').val();
                d.edad_min        = $('#edad_min').val();
                d.edad_max        = $('#edad_max').val();
                d.nivelEducativo  = $('#nivelEducativo').val();
                d.profesion       = $('#profesion').val();
                d.estadoCivil     = $('#estadoCivil').val();
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
    });

    $('#btnFiltrar').on('click', function() {
        table.ajax.reload();
    });

    $('#btnLimpiar').on('click', function () {
        $('input, select').val('');
        table.ajax.reload();
    });

    $('#btnExportar').on('click', function () {
        const params = new URLSearchParams({
            fecha_inicio: $('#filtro_fecha_inicio').val(),
            fecha_fin: $('#filtro_fecha_fin').val(),
            genero: $('#genero').val(),
            pais: $('#pais').val(),
            ciudad: $('#ciudad').val(),
            canal: $('#canal').val(),
            edad_min: $('#edad_min').val(),
            edad_max: $('#edad_max').val(),
            nivelEducativo: $('#nivelEducativo').val(),
            profesion: $('#profesion').val(),
            estadoCivil: $('#estadoCivil').val(),
            search: table.search()
        });

        window.location = `/suscriptores/exportar?${params.toString()}`;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {

    function cargarEstadistica() {
        let inicio = $('#grafica_fecha_inicio').val();
        let fin    = $('#grafica_fecha_fin').val();

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
