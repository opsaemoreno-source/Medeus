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
            <div class="d-flex gap-2 mb-3">
                <input type="date" id="fechaInicio" class="form-control form-control-sm" style="max-width: 180px;">
                <input type="date" id="fechaFin" class="form-control form-control-sm" style="max-width: 180px;">
                <button id="aplicarFiltroFecha" class="btn btn-sm btn-primary">
                    Aplicar
                </button>
            </div>

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

window._filtrosFechas = {
    marca: { inicio: null, fin: null },
    genero: { inicio: null, fin: null },
    estadoCivil: { inicio: null, fin: null },
    educacion: { inicio: null, fin: null },
    profesion: { inicio: null, fin: null },
    pais: { inicio: null, fin: null },
    ciudad: { inicio: null, fin: null },
    canal: { inicio: null, fin: null }
};

</script>
<script>
$(function () {

    // Función que carga el contenido dinámico
    function cargarEstadistica(url) {

        const params = new URLSearchParams();
        const key = window._pestaniaActiva;
        const fechas = window._filtrosFechas[key];

        if (fechas?.inicio && fechas?.fin) {
            params.append('fecha_inicio', fechas.inicio);
            params.append('fecha_fin', fechas.fin);
        }

        if (key === 'ciudad') {
            const modo = $('#modoCiudadSelect').val();
            params.append('modo_ciudad', modo);
        }

        const finalUrl = params.toString()
            ? `${url}?${params.toString()}`
            : url;

        $("#panelData").empty();
        $("#loader").fadeIn(150);

        $.ajax({
            url: finalUrl,
            method: "GET",
            success: function (response) {

                $("#loader").fadeOut(150);

                $("#panelData").html(response.html);

                if (response.data) {
                    window._suscriptoresData = response.data;
                    actualizarGraficosYTablas();
                }
            }
        });
    }

    $(document).on('change', '#modoCiudadSelect', function() {
        cargarEstadistica("/estadisticas/suscriptores");
    });


    // Botones del menú
    $("#btnEncuestas").click(() => cargarEstadistica("/estadisticas/encuestas"));
    $("#btnSuscriptores").click(() => cargarEstadistica("/estadisticas/suscriptores"));
    $("#btnAvanzado").click(() => cargarEstadistica("/estadisticas/avanzado"));

    $("#aplicarFiltroFecha").on("click", function () {

        const key = window._pestaniaActiva;

        if (!window._filtrosFechas[key]) {
            window._filtrosFechas[key] = { inicio: null, fin: null };
        }

        window._filtrosFechas[key].inicio = $("#fechaInicio").val();
        window._filtrosFechas[key].fin    = $("#fechaFin").val();

        // Vuelve a consultar backend
        cargarEstadistica("/estadisticas/suscriptores");
    });

});

// ============================
// FUNCIÓN QUE GENERA LOS CHARTS
// ============================

window._filtrosVacios = {
    marca: false,
    genero: false,
    estadoCivil: false,
    educacion: false,
    profesion: false,
    pais: false,
    ciudad: false,
    canal: false
};

const filtrar = (arr, clave) => {
    if (!arr) return [];
    if (window._filtrosVacios[clave]) return arr;

    return arr.filter(i => {
        const cat = (i.categoria ?? '').toString().trim();
        return cat !== '' && cat.toLowerCase() !== 'sin datos';
    });
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
        marca: filtrar(data.marca, 'marca'),
        genero: filtrar(data.genero, 'genero'),
        estadoCivil: filtrar(data.estadoCivil, 'estadoCivil'),
        nivelEducativo: filtrar(data.nivelEducativo, 'nivelEducativo'),
        profesion: filtrar(data.profesion, 'profesion'),
        pais: filtrar(data.pais, 'pais'),
        ciudad: filtrar(data.ciudad, 'ciudad'), 
        canal: filtrar(data.canal, 'canal')
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
    makeChart('chartCiudad', 'bar', datasets.ciudad);
    makeChart('chartCanal', 'bar', datasets.canal);

    // Tablas individuales
    crearTabla("tablaMarca", datasets.marca);
    crearTabla("tablaGenero", datasets.genero);
    crearTabla("tablaEstadoCivil", datasets.estadoCivil);
    crearTabla("tablaNivelEducativo", datasets.nivelEducativo);
    crearTabla("tablaProfesion", datasets.profesion);
    crearTabla("tablaPais", datasets.pais);
    crearTabla("tablaCiudad", datasets.ciudad);
    crearTabla("tablaCanal", datasets.canal);
}

$(document).on("click", ".toggle-vacios", function () {
    const key = $(this).data('key');
    window._filtrosVacios[key] = !window._filtrosVacios[key];
    $(this).text(
        window._filtrosVacios[key]
            ? "Excluir datos vacíos"
            : "Incluir datos vacíos"
    );
    // Solo redibuja todo con el nuevo estado
    actualizarGraficosYTablas();
});

$(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {
    window._pestaniaActiva = e.target.id.replace('tab-', '');

    const fechas = window._filtrosFechas[window._pestaniaActiva];

    $("#fechaInicio").val(fechas.inicio);
    $("#fechaFin").val(fechas.fin);
});

</script>
@endsection
