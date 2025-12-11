@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">

        {{-- SIDEBAR IZQUIERDO --}}
        <div class="col-md-3 col-lg-2 bg-light border-end d-flex flex-column p-3" style="min-height: 100vh;">

            <h5 class="text-uppercase fw-bold mb-3 mt-2">Estadísticas</h5>

            <div class="list-group">

                <button class="list-group-item list-group-item-action py-3" id="btnEncuestas">
                    Encuestas
                </button>

                <button class="list-group-item list-group-item-action py-3" id="btnSuscriptores">
                    Suscriptores
                </button>

                <button class="list-group-item list-group-item-action py-3" id="btnAvanzado">
                    Avanzado
                </button>

            </div>
        </div>

        {{-- PANEL PRINCIPAL --}}
        <div class="col-md-9 col-lg-10 py-4" id="contenidoEstadisticas">

            <h3 class="mb-4">Seleccione una opción del menú</h3>

            {{-- Loader --}}
            <div id="loader" class="text-center my-5" style="display: none;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 fs-5">Cargando...</p>
            </div>

            {{-- Aquí se mostrará el contenido dinámico --}}
            <div id="panelData" class="mt-4"></div>

        </div>
    </div>

</div>
@endsection


@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(function () {

    // Función que carga el contenido dinámico
    function cargarEstadistica(url) {
        $("#panelData").empty();
        $("#loader").fadeIn(150);

        $.ajax({
            url: url,
            method: "GET",
            success: function (response) {

                $("#loader").fadeOut(150);

                // Si la respuesta contiene HTML
                if (response.html) {
                    $("#panelData").html(response.html);
                } else {
                    $("#panelData").html("<pre>" + JSON.stringify(response, null, 2) + "</pre>");
                }

                // Si vienen datos para los charts
                if (response.data) {
                    renderSuscriptoresCharts(response.data);
                }
            },
            error: function () {
                $("#loader").hide();
                $("#panelData").html(`
                    <div class='alert alert-danger mt-4'>
                        Error al cargar los datos.
                    </div>
                `);
            }
        });
    }

    // Botones del menú
    $("#btnEncuestas").click(() => cargarEstadistica("/estadisticas/encuestas"));
    $("#btnSuscriptores").click(() => cargarEstadistica("/estadisticas/suscriptores"));
    $("#btnAvanzado").click(() => cargarEstadistica("/estadisticas/avanzado"));

});


// ============================
// FUNCIÓN QUE GENERA LOS CHARTS
// ============================

function renderSuscriptoresCharts(estadisticas) {

    if (!estadisticas) return;

    // Helper para generar gráficos
    const makeChart = (id, type, labels, values) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 205, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(201, 203, 207, 0.6)'
                    ]
                }]
            }
        });
    };

    makeChart('chartMarca', 'doughnut', estadisticas.marca?.map(i => i.categoria), estadisticas.marca?.map(i => i.total));
    makeChart('chartGenero', 'doughnut', estadisticas.genero?.map(i => i.categoria), estadisticas.genero?.map(i => i.total));
    makeChart('chartEstadoCivil', 'bar', estadisticas.estadoCivil?.map(i => i.categoria), estadisticas.estadoCivil?.map(i => i.total));
    makeChart('chartNivelEducativo', 'bar', estadisticas.nivelEducativo?.map(i => i.categoria), estadisticas.nivelEducativo?.map(i => i.total));
    makeChart('chartProfesion', 'bar', estadisticas.profesion?.map(i => i.categoria), estadisticas.profesion?.map(i => i.total));
    makeChart('chartPais', 'bar', estadisticas.pais?.map(i => i.categoria), estadisticas.pais?.map(i => i.total));
    makeChart('chartCanal', 'bar', estadisticas.canal?.map(i => i.categoria), estadisticas.canal?.map(i => i.total));
}
</script>
@endsection
