<div>
    <h3 class="mb-4">Estadísticas de Encuestas</h3>

    {{-- KPIs --}}
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                <h6>% Completación</h6>
                <h3>{{ $data['kpis']['porcentaje_completacion'] }}%</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>Usuarios que respondieron</h6>
                <h3>{{ $data['kpis']['usuarios_respondieron'] }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6>Total usuarios registrados</h6>
                <h3>{{ $data['kpis']['total_usuarios'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row">
        <div class="col-md-6 mb-4">
            <h5>Género</h5>
            <canvas id="chartEncuestasGenero"></canvas>
        </div>
        <div class="col-md-6 mb-4">
            <h5>País</h5>
            <canvas id="chartEncuestasPais"></canvas>
        </div>
        <div class="col-md-6 mb-4">
            <h5>Ciudad</h5>
            <canvas id="chartEncuestasCiudad"></canvas>
        </div>
        <div class="col-md-6 mb-4">
            <h5>Nivel educativo</h5>
            <canvas id="chartEncuestasEducacion"></canvas>
        </div>
    </div>
</div>

<script>
(function () {

    const charts = {};

    const render = (id, items, type = 'bar') => {
        const ctx = document.getElementById(id);
        if (!ctx || !items) return;

        if (charts[id]) charts[id].destroy();

        charts[id] = new Chart(ctx, {
            type,
            data: {
                labels: items.map(i => i.categoria),
                datasets: [{
                    data: items.map(i => i.total)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    };

    render('chartEncuestasGenero', @json($data['genero']), 'doughnut');
    render('chartEncuestasPais', @json($data['pais']));
    render('chartEncuestasCiudad', @json($data['ciudad']));
    render('chartEncuestasEducacion', @json($data['nivelEducativo']));

})();
</script>
