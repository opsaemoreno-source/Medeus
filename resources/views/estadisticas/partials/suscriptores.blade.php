<div>
    <h3 class="mb-4">Estadísticas de Suscriptores</h3>

    <p class="text-muted">Distribuciones, conteos y comportamiento general de los usuarios registrados.</p>

    {{-- Suscriptores por Marca --}}
    <div class="mb-5">
        <h5>Suscriptores por Marca</h5>
        <canvas id="chartMarca"></canvas>
    </div>

    {{-- Suscriptores por Género --}}
    <div class="mb-5">
        <h5>Suscriptores por Género</h5>
        <canvas id="chartGenero"></canvas>
    </div>

    {{-- Estado Civil --}}
    <div class="mb-5">
        <h5>Estado Civil</h5>
        <canvas id="chartEstadoCivil"></canvas>
    </div>

    {{-- Nivel Educativo --}}
    <div class="mb-5">
        <h5>Nivel Educativo</h5>
        <canvas id="chartNivelEducativo"></canvas>
    </div>

    {{-- Profesión --}}
    <div class="mb-5">
        <h5>Profesión</h5>
        <canvas id="chartProfesion"></canvas>
    </div>

    {{-- País --}}
    <div class="mb-5">
        <h5>País</h5>
        <canvas id="chartPais"></canvas>
    </div>

    {{-- Canal (web, app, orgánico, etc.) --}}
    <div class="mb-5">
        <h5>Canal donde ingresó el Suscriptor</h5>
        <canvas id="chartCanal"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const estadisticas = @json($estadisticas);

    // Suscriptores por Marca
    const ctxMarca = document.getElementById('chartMarca').getContext('2d');
    new Chart(ctxMarca, {
        type: 'doughnut',
        data: {
            labels: estadisticas.marca.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.marca.map(i => i.total),
                backgroundColor: ["#4e73df", "#1cc88a", "#858796"]
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Suscriptores por Género
    const ctxGenero = document.getElementById('chartGenero').getContext('2d');
    new Chart(ctxGenero, {
        type: 'doughnut',
        data: {
            labels: estadisticas.genero.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.genero.map(i => i.total),
                backgroundColor: ["#36b9cc", "#f6c23e", "#858796"]
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Estado Civil
    const ctxEstadoCivil = document.getElementById('chartEstadoCivil').getContext('2d');
    new Chart(ctxEstadoCivil, {
        type: 'bar',
        data: {
            labels: estadisticas.estadoCivil.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.estadoCivil.map(i => i.total),
                backgroundColor: "#4e73df"
            }]
        },
        options: { responsive: true }
    });

    // Nivel Educativo
    const ctxNivelEdu = document.getElementById('chartNivelEducativo').getContext('2d');
    new Chart(ctxNivelEdu, {
        type: 'bar',
        data: {
            labels: estadisticas.nivelEducativo.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.nivelEducativo.map(i => i.total),
                backgroundColor: "#1cc88a"
            }]
        },
        options: { responsive: true }
    });

    // Profesión
    const ctxProf = document.getElementById('chartProfesion').getContext('2d');
    new Chart(ctxProf, {
        type: 'bar',
        data: {
            labels: estadisticas.profesion.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.profesion.map(i => i.total),
                backgroundColor: "#36b9cc"
            }]
        },
        options: { responsive: true }
    });

    // País
    const ctxPais = document.getElementById('chartPais').getContext('2d');
    new Chart(ctxPais, {
        type: 'bar',
        data: {
            labels: estadisticas.pais.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.pais.map(i => i.total),
                backgroundColor: "#f6c23e"
            }]
        },
        options: { responsive: true }
    });

    // Canal
    const ctxCanal = document.getElementById('chartCanal').getContext('2d');
    new Chart(ctxCanal, {
        type: 'bar',
        data: {
            labels: estadisticas.canal.map(i => i.categoria),
            datasets: [{
                label: "Suscriptores",
                data: estadisticas.canal.map(i => i.total),
                backgroundColor: "#858796"
            }]
        },
        options: { responsive: true }
    });

});
</script>

