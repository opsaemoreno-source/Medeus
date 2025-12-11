{{-- PESTAÑAS DE GRÁFICOS --}}
<ul class="nav nav-tabs" id="chartsTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="tab-marca" data-bs-toggle="tab" data-bs-target="#pane-marca" type="button">
            Marca
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link" id="tab-genero" data-bs-toggle="tab" data-bs-target="#pane-genero" type="button">
            Género
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link" id="tab-estado" data-bs-toggle="tab" data-bs-target="#pane-estado" type="button">
            Estado civil
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link" id="tab-educacion" data-bs-toggle="tab" data-bs-target="#pane-educacion" type="button">
            Nivel educativo
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link" id="tab-profesion" data-bs-toggle="tab" data-bs-target="#pane-profesion" type="button">
            Profesión
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link" id="tab-pais" data-bs-toggle="tab" data-bs-target="#pane-pais" type="button">
            País
        </button>
    </li>

    <li class="nav-item">
        <button class="nav-link" id="tab-canal" data-bs-toggle="tab" data-bs-target="#pane-canal" type="button">
            Canal
        </button>
    </li>
</ul>

{{-- CONTENIDO DE LAS PESTAÑAS --}}
<div class="tab-content mt-4">

    <div class="tab-pane fade show active" id="pane-marca">
        <div class="chart-wrapper">
            <canvas id="chartMarca"></canvas>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-genero">
        <div class="chart-wrapper">
            <canvas id="chartGenero"></canvas>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-estado">
        <div class="chart-wrapper">
            <canvas id="chartEstadoCivil"></canvas>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-educacion">
        <div class="chart-wrapper">
            <canvas id="chartNivelEducativo"></canvas>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-profesion">
        <div class="chart-wrapper">
            <canvas id="chartProfesion"></canvas>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-pais">
        <div class="chart-wrapper">
            <canvas id="chartPais"></canvas>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-canal">
        <div class="chart-wrapper">
            <canvas id="chartCanal"></canvas>
        </div>
    </div>

</div>

{{-- CSS para controlar el tamaño --}}
<style>
.chart-wrapper {
    max-width: 420px;      /* Tamaño más reducido */
    height: 320px;         /* Controla el diámetro de la dona */
    margin: 0 auto;
    position: relative;
}
</style>
