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

let incluirVacios = false;   // Estado global: incluir o excluir "Sin datos"

const filtrar = (arr) => {
    if (!arr) return [];
    return incluirVacios ? arr : arr.filter(i => i.categoria !== 'Sin datos');
};

function renderSuscriptoresCharts(estadisticas) {
    if (!estadisticas) return;

    // Guardamos los datos "raw" para construir tabla después
    window._suscriptoresData = estadisticas;

    actualizarGraficos();
    actualizarTabla();
}

function crearTabla(idContenedor, items) {
    if (!items || !Array.isArray(items)) return;

    let html = `
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Categoría</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
    `;

    items.forEach(item => {
        html += `
            <tr>
                <td>${item.categoria}</td>
                <td>${item.total}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    document.getElementById(idContenedor).innerHTML = html;
}

function renderSuscriptoresCharts(estadisticas) {
    window._suscriptoresData = estadisticas;
    actualizarGraficosYTablas();
}

function actualizarGraficosYTablas() {
    const data = window._suscriptoresData;
    if (!data) return;

    const datasets = {
        marca: filtrar(data.marca),
        genero: filtrar(data.genero),
        estadoCivil: filtrar(data.estadoCivil),
        nivelEducativo: filtrar(data.nivelEducativo),
        profesion: filtrar(data.profesion),
        pais: filtrar(data.pais),
        canal: filtrar(data.canal)
    };

    // Reiniciar los canvas para evitar duplicados
    $('canvas').each(function () {
        const newCanvas = $(this).clone();
        $(this).replaceWith(newCanvas);
    });

    const makeChart = (id, type, items) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: type,
            data: {
                labels: items.map(i => i.categoria),
                datasets: [{
                    data: items.map(i => i.total),
                    backgroundColor: [
                        'rgba(54,162,235,0.6)',
                        'rgba(255,99,132,0.6)',
                        'rgba(255,205,86,0.6)',
                        'rgba(75,192,192,0.6)',
                        'rgba(153,102,255,0.6)',
                        'rgba(201,203,207,0.6)'
                    ]
                }]
            }
        });
    };

    // Gráficos individuales
    makeChart('chartMarca', 'doughnut', datasets.marca);
    makeChart('chartGenero', 'doughnut', datasets.genero);
    makeChart('chartEstadoCivil', 'bar', datasets.estadoCivil);
    makeChart('chartNivelEducativo', 'bar', datasets.nivelEducativo);
    makeChart('chartProfesion', 'bar', datasets.profesion);
    makeChart('chartPais', 'bar', datasets.pais);
    makeChart('chartCanal', 'bar', datasets.canal);

    // Tablas individuales
    crearTabla("tablaMarca", datasets.marca);
    crearTabla("tablaGenero", datasets.genero);
    crearTabla("tablaEstadoCivil", datasets.estadoCivil);
    crearTabla("tablaNivelEducativo", datasets.nivelEducativo);
    crearTabla("tablaProfesion", datasets.profesion);
    crearTabla("tablaPais", datasets.pais);
    crearTabla("tablaCanal", datasets.canal);
}

$(document).on("click", "#toggleVacios", function () {
    incluirVacios = !incluirVacios;
    $(this).text(incluirVacios ? "Excluir datos vacíos" : "Incluir datos vacíos");
    actualizarGraficosYTablas();
});
</script>
@endsection
