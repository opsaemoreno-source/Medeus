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
        <button class="nav-link" id="tab-ciudad" data-bs-toggle="tab" data-bs-target="#pane-ciudad" type="button">
            Ciudad
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
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="marca">
                Incluir datos vacíos
            </button>
        </div>

        <div class="chart-wrapper">
            <canvas id="chartMarca"></canvas>
        </div>

        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaMarca"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-genero">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="genero">
                Incluir datos vacíos
            </button>
        </div>
        <div class="chart-wrapper">
            <canvas id="chartGenero"></canvas>
        </div>
        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaGenero"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-estado">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="estadoCivil">
                Incluir datos vacíos
            </button>
        </div>
        <div class="chart-wrapper">
            <canvas id="chartEstadoCivil"></canvas>
        </div>
        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaEstadoCivil"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-educacion">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="educacion">
                Incluir datos vacíos
            </button>
        </div>
        <div class="chart-wrapper">
            <canvas id="chartNivelEducativo"></canvas>
        </div>
        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaNivelEducativo"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-profesion">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="profesion">
                Incluir datos vacíos
            </button>
        </div>
        <div class="chart-wrapper">
            <canvas id="chartProfesion"></canvas>
        </div>
        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaProfesion"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-pais">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="pais">
                Incluir datos vacíos
            </button>
        </div>
        <div class="chart-wrapper">
            <canvas id="chartPais"></canvas>
        </div>
        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaPais"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-ciudad">
        <div class="d-flex justify-content-between mb-2">
            <div>
                <button class="btn btn-dark btn-sm toggle-vacios"
                        data-key="ciudad">
                    Incluir datos vacíos
                </button>
            </div>
            <div>
                <select id="modoCiudadSelect" class="form-select form-select-sm">
                    <option value="normalizado" {{ ($modoCiudad ?? 'normalizado') == 'normalizado' ? 'selected' : '' }}>Normalizado</option>
                    <option value="original" {{ ($modoCiudad ?? '') == 'original' ? 'selected' : '' }}>Original</option>
                </select>
            </div>
        </div>

        <div class="chart-wrapper">
            <canvas id="chartCiudad"></canvas>
        </div>

        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaCiudad"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-canal">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-dark btn-sm toggle-vacios"
                    data-key="canal">
                Incluir datos vacíos
            </button>
        </div>
        <div class="chart-wrapper">
            <canvas id="chartCanal"></canvas>
        </div>
        <div class="mt-4">
            <h5 class="fw-bold">Datos</h5>
            <div id="tablaCanal"></div>
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
