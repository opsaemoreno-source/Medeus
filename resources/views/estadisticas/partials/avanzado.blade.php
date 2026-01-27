@php
    use Carbon\Carbon;

    $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
@endphp
<div class="container-fluid">

    <h3 class="mb-4">Estadísticas Avanzadas</h3>

    {{-- FILTROS DEMOGRÁFICOS --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2">

                <div class="col-md-2">
                    <label>Fecha Inicio</label>
                    <input type="date" id="avanzadoFechaInicio" class="form-control form-control-sm" value="{{ $fechaInicio }}">
                </div>
                <div class="col-md-2">
                    <label>Fecha Fin</label>
                    <input type="date" id="avanzadoFechaFin" class="form-control form-control-sm" value="{{ $fechaFin }}">
                </div>
                <div class="col-md-2">
                    <label>Marca</label>
                    <select id="avanzadoMarca" class="form-control form-control-sm"></select>
                </div>
                <div class="col-md-2">
                    <label>Género</label>
                    <select id="avanzadoGenero" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Edad</label>
                    <select id="avanzadoEdadRango" class="form-control form-control-sm">
                        <option value="">Todas las edades</option>
                        <option value="18-24">18 – 24</option>
                        <option value="25-34">25 – 34</option>
                        <option value="35-44">35 – 44</option>
                        <option value="45-54">45 – 54</option>
                        <option value="55-64">55 – 64</option>
                        <option value="65+">65+</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="avanzadoRespondieronEncuesta">
                        <label class="form-check-label" for="avanzadoRespondieronEncuesta">
                            Respondieron Encuestas
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-2">
                    <label>Estado Civil</label>
                    <select id="avanzadoEstadoCivil" class="form-control form-control-sm"></select>
                </div>
                <div class="col-md-2">
                    <label>Nivel Educativo</label>
                    <select id="avanzadoNivelEducativo" class="form-control form-control-sm"></select>
                </div>
                <div class="col-md-2">
                    <label>Profesión</label>
                    <select id="avanzadoProfesion" class="form-control form-control-sm"></select>
                </div>
                <div class="col-md-2">
                    <label>País</label>
                    <select id="avanzadoPais" class="form-control form-control-sm"></select>
                </div>
                <div class="col-md-2">
                    <label>Ciudad</label>
                    <input type="text" id="avanzadoCiudad" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label>Canal</label>
                    <select id="avanzadoCanal" class="form-control form-control-sm"></select>
                </div>
            </div>

            <div class="mt-3">
                <button id="aplicarFiltrosAvanzado" class="btn btn-primary btn-sm">Aplicar filtros</button>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row mb-4" id="avanzadoKPIs">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Usuarios</h5>
                    <h3 id="kpiUsuarios">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Compras</h5>
                    <h3 id="kpiCompras">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4" id="kpiUserEncuestas">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">
                        Usuarios que respondieron encuestas
                    </h6>
                    <h2 id="kpiUsuariosEncuestas">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Total Suscripciones</h6>
                    <h3 id="kpiSuscripciones">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Monto Suscripciones (USD)</h6>
                    <h3 id="kpiMontoUSD">$0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Monto Suscripciones (HNL)</h6>
                    <h3 id="kpiMontoHNL">L 0</h3>
                </div>
            </div>
        </div>
    </div>


    {{-- GRÁFICOS --}}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Top 10 Países (Perfil)</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>País</th>
                                <th class="text-end">Usuarios</th>
                            </tr>
                        </thead>
                        <tbody id="tablaTopPaisPerfil"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Top 10 Países (IP)</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>País</th>
                                <th class="text-end">Usuarios</th>
                            </tr>
                        </thead>
                        <tbody id="tablaTopPaisIP"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <strong>Top 10 Ciudades con más usuarios</strong>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Ciudad</th>
                        <th class="text-end">Usuarios</th>
                    </tr>
                </thead>
                <tbody id="tablaTopCiudades">
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            Sin datos
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">
            <strong>Top 10 Profesiones</strong>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Profesión</th>
                        <th class="text-end">Usuarios</th>
                    </tr>
                </thead>
                <tbody id="tablaTopProfesiones">
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            Sin datos
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <strong>Top 10 Nivel Educativo</strong>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Nivel Educativo</th>
                        <th class="text-end">Usuarios</th>
                    </tr>
                </thead>
                <tbody id="tablaTopNivelesEducativos">
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            Sin datos
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    $(document).on('click', '#aplicarFiltrosAvanzado', function () {
        cargarEstadisticasAvanzadas();
    });
</script>