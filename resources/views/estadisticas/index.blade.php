@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">

        {{-- SIDEBAR IZQUIERDO --}}
        <div class="col-md-3 col-lg-2 bg-light border-end d-flex flex-column p-3" style="min-height: 100vh;">

            <h5 class="text-uppercase fw-bold mb-3 mt-2">Estadísticas</h5>

            <div class="list-group">
                <button class="list-group-item list-group-item-action py-3 tab-btn" data-tab="encuestas">
                    Encuestas
                </button>
                <button class="list-group-item list-group-item-action py-3 tab-btn" data-tab="suscriptores">
                    Usuarios
                </button>
                <button class="list-group-item list-group-item-action py-3 tab-btn" data-tab="compras">
                    Compras
                </button>
                <button class="list-group-item list-group-item-action py-3 tab-btn" data-tab="avanzado">
                    Avanzado
                </button>
            </div>
        </div>

        {{-- PANEL PRINCIPAL --}}
        <div class="col-md-9 col-lg-10 py-4" id="contenidoEstadisticas">

            <h3 class="mb-4">Seleccione una opción del menú</h3>
            <div class="row mb-3" id="filtroFechasGeneral">
                <div class="col-md-3">
                    <input type="date" id="fechaInicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="date" id="fechaFin" class="form-control">
                </div>
                <div class="col-md-2">
                    <button id="aplicarFiltroFecha" class="btn btn-primary">Aplicar</button>
                </div>
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
    canal: { inicio: null, fin: null },
    suscriptores: { inicio: null, fin: null },
    compras: { inicio: null, fin: null }
};

window._chartsCompras = {
    porDia: null,
    porProducto: null,
    porEstado: null,
    porMarca: null,
    porCanal: null,
    porFrecuencia: null
};

window._filtrosAvanzado = {
    fecha_inicio: null,
    fecha_fin: null,
    marca: null,
    genero: null,
    estadoCivil: null,
    nivelEducativo: null,
    profesion: null,
    pais: null,
    ciudad: null,
    canal: null,
    edad_min: null,
    edad_max: null
};

window._chartsAvanzado = {};

window._chartsSuscriptores = {};

window._modoCompras = 'cantidad'; // cantidad | valor

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

                if (!response.data) return;

                if (window._pestaniaActiva === 'suscriptores') {
                    window._suscriptoresData = response.data;
                    actualizarGraficosYTablas();
                }

                if (window._pestaniaActiva === 'compras') {
                    window._comprasData = response.data;
                    window._comprasActivasData = response.activas;
                    renderComprasCharts(window._comprasData, 'HNL', window._comprasActivasData);
                }

                if (window._pestaniaActiva === 'avanzado') {
                    setTimeout(() => {
                        $('#avanzadoFechaInicio').val($('#fechaInicio').val());
                        $('#avanzadoFechaFin').val($('#fechaFin').val());
                        cargarEstadisticasAvanzadas();
                    }, 0);
                }

            }
        });
    }

    $(document).on('change', '#modoCiudadSelect', function() {
        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    });


    // Botones del menú
    $("#btnEncuestas").click(() => {
        window._pestaniaActiva = 'encuestas';
        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    });

    $("#btnSuscriptores").click(() => {
        window._pestaniaActiva = 'suscriptores';
        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    });

    $("#btnCompras").click(() => {
        window._pestaniaActiva = 'compras';
        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    });

    $("#btnAvanzado").click(() => {
        window._pestaniaActiva = 'avanzado';
        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    });


    $("#aplicarFiltroFecha").on("click", function () {

        const key = window._pestaniaActiva;

        if (!window._filtrosFechas[key]) {
            window._filtrosFechas[key] = { inicio: null, fin: null };
        }

        window._filtrosFechas[key].inicio = $("#fechaInicio").val();
        window._filtrosFechas[key].fin    = $("#fechaFin").val();

        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    });

    function onTabChange(tab) {
        window._pestaniaActiva = tab;

        if (tab === 'avanzado') {
            $('#filtroFechasGeneral').hide();
        } else {
            $('#filtroFechasGeneral').show();
        }

        cargarEstadistica(`/estadisticas/${window._pestaniaActiva}`);
    }

    $(document).ready(function () {
        const initialTab = window._pestaniaActiva || 'encuestas';
        onTabChange(initialTab);
    });


    $(document).on('click', '.tab-btn', function () {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');

        const tab = $(this).data('tab');
        onTabChange(tab);
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

function renderComprasCharts(data, moneda, activas) {
    if (!data || !data[moneda]) return;

    const modo = window._modoCompras;
    const d = data[moneda][modo];
    const da = activas?.[moneda]?.[modo];

    const simbolo = moneda === 'HNL' ? 'L' : '$';
    const sufijo  = modo === 'valor'
        ? `(${simbolo})`
        : '(Cantidad)';

    const chartsMap = {
        comprasDia: 'porDia',
        comprasProducto: 'porProducto',
        comprasEstado: 'porEstado',
        comprasMarca: 'porMarca',
        comprasCanal: 'porCanal',
        comprasFrecuencia: 'porFrecuencia'
    };

    const render = (canvasId, key, labels, values, type = 'bar', labelTexto = '') => {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // destruir SOLO el gráfico correcto
        if (window._chartsCompras[key]) {
            window._chartsCompras[key].destroy();
        }

        window._chartsCompras[key] = new Chart(ctx, {
            type,
            data: {
                labels,
                datasets: [{
                    label: labelTexto,
                    data: values
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    };

    render(
        'comprasDia',
        'porDia',
        Object.keys(da.porDia),
        Object.values(da.porDia),
        'line',
        `Suscripciones por día ${sufijo}`
    );

    render(
        'comprasProducto',
        'porProducto',
        Object.keys(da.porProducto),
        Object.values(da.porProducto),
        'bar',
        `Suscripciones por producto ${sufijo}`
    );

    render(
        'comprasEstado',
        'porEstado',
        Object.keys(d.porEstado),
        Object.values(d.porEstado),
        'bar',
        `Suscripciones por estado ${sufijo}`
    );

    render(
        'comprasMarca',
        'porMarca',
        Object.keys(da.porMarca),
        Object.values(da.porMarca),
        'bar',
        `Suscripciones por marca ${sufijo}`
    );

    render(
        'comprasCanal',
        'porCanal',
        Object.keys(da.porCanal),
        Object.values(da.porCanal),
        'bar',
        `Suscripciones por canal ${sufijo}`
    );

    render(
        'comprasFrecuencia',
        'porFrecuencia',
        Object.keys(da.porFrecuencia),
        Object.values(da.porFrecuencia),
        'bar',
        `Suscripciones por frecuencia ${sufijo}`
    );
}

$(document).on('click', '#tabsMoneda button', function () {
    $('#tabsMoneda button').removeClass('active');
    $(this).addClass('active');
    const moneda = $(this).data('moneda');
    renderComprasCharts(window._comprasData, moneda, window._comprasActivasData);
});

function renderComprasPorDia(labels, data, labelTexto) {
    const ctx = document.getElementById('comprasDia');

    if (window._chartsCompras.porDia) {
        window._chartsCompras.porDia.destroy();
    }

    window._chartsCompras.porDia = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: labelTexto,
                data: data,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
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
    if (!estadisticas) return;
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
    /*$('canvas').each(function () {
        const newCanvas = $(this).clone();
        $(this).replaceWith(newCanvas);
    });*/

    const makeChart = (id, type, items) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;

        if (window._chartsSuscriptores[id]) {
            window._chartsSuscriptores[id].destroy();
        }

        window._chartsSuscriptores[id] = new Chart(ctx, {
            type,
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
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
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

$(document).on('click', '[data-modo]', function () {
    $('[data-modo]').removeClass('active');
    $(this).addClass('active');

    window._modoCompras = $(this).data('modo');

    const moneda = $('#tabsMoneda .active').data('moneda');
    renderComprasCharts(window._comprasData, moneda, window._comprasActivasData);
});

function getFiltrosAvanzado() {
    let edadMin = null;
    let edadMax = null;
    const rango = $('#avanzadoEdadRango').val();
    if (rango) {
        if (rango.includes('+')) {
            edadMin = parseInt(rango.replace('+', ''));
            edadMax = null;
        } else {
            const partes = rango.split('-');
            edadMin = parseInt(partes[0]);
            edadMax = parseInt(partes[1]);
        }
    }

    return {
        fecha_inicio: $('#avanzadoFechaInicio').val(),
        fecha_fin: $('#avanzadoFechaFin').val(),
        marca: $('#avanzadoMarca').val(),
        genero: $('#avanzadoGenero').val(),
        edad_min: edadMin,
        edad_max: edadMax,
        estadoCivil: $('#avanzadoEstadoCivil').val(),
        nivelEducativo: $('#avanzadoNivelEducativo').val(),
        profesion: $('#avanzadoProfesion').val(),
        pais: $('#avanzadoPais').val(),
        ciudad: $('#avanzadoCiudad').val(),
        canal: $('#avanzadoCanal').val()
    };
}

function cargarEstadisticasAvanzadas() {
    const filtros = getFiltrosAvanzado();
    let htmlPerfil = '';
    let htmlIP = '';
    
    $.ajax({
        url: '/estadisticas/avanzado',
        method: 'GET',
        data: filtros,
        beforeSend: () => {
            $('#avanzadoKPIs h3').text('Cargando...');
        },
        success: function(res) {
            // KPIs
            $('#kpiUsuarios').text(res.data.usuariosMixtos.total_usuarios || 0);
            $('#kpiCompras').text(res.data.usuariosMixtos.total_compras || 0);
            $('#kpiUsuariosEncuestas').text(res.data.usuariosRespondieronEncuesta ?? 0);

            if (res.data.suscripciones) {
                $('#kpiSuscripciones').text(
                    res.data.suscripciones.total_suscripciones || 0
                );

                $('#kpiMontoUSD').text(
                    '$ ' + (res.data.suscripciones.monto_usd || 0).toLocaleString()
                );

                $('#kpiMontoHNL').text(
                    'L ' + (res.data.suscripciones.monto_hnl || 0).toLocaleString()
                );
            }
            if (res.data.topCiudades) {
                renderTopCiudades(res.data.topCiudades);
            }
            if (res.data.topProfesiones) {
                renderTopProfesiones(res.data.topProfesiones);
            }
            if (res.data.topNivelesEducativos) {
                renderTopNivelesEducativos(res.data.topNivelesEducativos);
            }

            const comprasRespuesta = res.data.comprasPorRespuesta || [];

            // Limitar visualización si hay muchas respuestas
            const topRespuestas = comprasRespuesta.slice(0, 20);
            
            // Llenar dropdown Marca
            const selectMarca = $('#avanzadoMarca');
            selectMarca.empty();
            selectMarca.append(`<option value="">Todos</option>`);
            (res.data.marcas || []).forEach(m => selectMarca.append(`<option value="${m}">${m}</option>`));

            // Llenar dropdown Canal
            const selectCanal = $('#avanzadoCanal');
            selectCanal.empty();
            selectCanal.append(`<option value="">Todos</option>`);
            (res.data.canales || []).forEach(c => selectCanal.append(`<option value="${c}">${c}</option>`));

            (res.data.topPaisesPerfil || []).forEach(row => {
                htmlPerfil += `
                    <tr>
                        <td>${row.pais}</td>
                        <td class="text-end">${row.total}</td>
                    </tr>`;
            });
            $('#tablaTopPaisPerfil').html(htmlPerfil);
            // Top Países IP
            
            (res.data.topPaisesIP || []).forEach(row => {
                htmlIP += `
                    <tr>
                        <td>${row.pais}</td>
                        <td class="text-end">${row.total}</td>
                    </tr>`;
            });
            $('#tablaTopPaisIP').html(htmlIP);

            // Estado Civil
            const selEstado = $('#avanzadoEstadoCivil');
            selEstado.empty().append('<option value="">Todos</option>');
            (res.data.catalogos.estadoCivil || []).forEach(e =>
                selEstado.append(`<option value="${e.id}">${e.label}</option>`)
            );

            // Nivel Educativo
            const selNivel = $('#avanzadoNivelEducativo');
            selNivel.empty().append('<option value="">Todos</option>');
            (res.data.catalogos.nivelEducativo || []).forEach(e =>
                selNivel.append(`<option value="${e.id}">${e.label}</option>`)
            );

            // Profesión
            const selProf = $('#avanzadoProfesion');
            selProf.empty().append('<option value="">Todos</option>');
            (res.data.catalogos.profesion || []).forEach(e =>
                selProf.append(`<option value="${e.id}">${e.label}</option>`)
            );

            // País
            const selPais = $('#avanzadoPais');
            selPais.empty().append('<option value="">Todos</option>');
            (res.data.catalogos.pais || []).forEach(e =>
                selPais.append(`<option value="${e.id}">${e.label}</option>`)
            );

        },
        error: function(err) {
            console.error('Error al cargar estadísticas avanzadas', err);
        }
    });
}

function renderTopCiudades(data) {
    let html = '';

    data.forEach(row => {
        html += `
            <tr>
                <td>${row.ciudad}</td>
                <td class="text-end">${row.total}</td>
            </tr>
        `;
    });

    $('#tablaTopCiudades').html(html);
}

function renderTopProfesiones(data) {
    let html = '';

    data.forEach(row => {
        html += `
            <tr>
                <td>${row.profesion}</td>
                <td class="text-end">${row.total}</td>
            </tr>
        `;
    });

    $('#tablaTopProfesiones').html(html);
}

function renderTopNivelesEducativos(data) {
    let html = '';

    data.forEach(row => {
        html += `
            <tr>
                <td>${row.nivelEducativo}</td>
                <td class="text-end">${row.total}</td>
            </tr>
        `;
    });

    $('#tablaTopNivelesEducativos').html(html);
}

</script>
@endsection
