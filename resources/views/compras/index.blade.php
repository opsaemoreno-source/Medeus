@php
    use Carbon\Carbon;

    $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
@endphp
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    <h4 class="mb-3">Compras</h4>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="filtrosCompras" class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{$fechaInicio}}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{$fechaFin}}">
                </div>

                {{-- Estado --}}
                <div class="col-md-3">
                    <label class="form-label">Estado</label>

                    <div class="dropdown w-100">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100"
                                type="button"
                                data-bs-toggle="dropdown">
                            Seleccionar estados
                        </button>

                        <ul class="dropdown-menu w-100 px-2">
                            @foreach (['ACTIVE','ACTIVE_PENDING','CANCELLED','CANCEL_PENDING','INCOMPLETE'] as $estado)
                                <li>
                                    <label class="form-check">
                                        <input type="checkbox"
                                            class="form-check-input estado-checkbox"
                                            value="{{ $estado }}">
                                        {{ $estado }}
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div id="estadoHiddenInputs"></div>
                </div>

                {{-- Marca --}}
                <div class="col-md-2">
                    <label class="form-label">Marca</label>
                    <select name="marca" class="form-select">
                        <option value="">Todas</option>
                        <option value="elheraldo">El Heraldo</option>
                        <option value="laprensa">La Prensa</option>
                    </select>
                </div>

                {{-- Canal --}}
                <div class="col-md-2">
                    <label class="form-label">Canal</label>
                    <select name="canal" class="form-select">
                        <option value="">Todos</option>
                        <option value="WEB">WEB</option>
                        <option value="ANDROID_MOBILE_APP">Android App</option>
                        <option value="IOS_MOBILE_APP">iOS App</option>
                        <option value="CUSTOMER_CARE">Customer Care</option>
                    </select>
                </div>

                {{-- Tipo pago --}}
                <div class="col-md-3">
                    <label class="form-label">Tipo de pago</label>
                    <select name="tipoPago" class="form-select">
                        <option value="">Todos</option>
                        <option value="GOOGLE_PAY_IN_APP">Google Pay</option>
                        <option value="PAYMENT_GATEWAY">Billetera Digital</option>
                        <option value="BANK_TRANSFER">Transferencia</option>
                        <option value="CARD">Tarjeta Débito/Crédito</option>
                        <option value="CASH">Efectivo</option>
                    </select>
                </div>

                {{-- Búsqueda --}}
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="ID Usuario o Compra">
                </div>

                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Buscar
                    </button>
                    <button type="button" id="limpiarFiltros" class="btn btn-outline-secondary">
                        Limpiar
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Totales --}}
    <div class="row mb-3">
        {{-- HISTÓRICOS --}}
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body">
                    <h6 class="text-muted">Ingresos históricos (HNL)</h6>
                    <h3 id="totalHNLHistorico">—</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body">
                    <h6 class="text-muted">Ingresos históricos (USD)</h6>
                    <h3 id="totalUSDHistorico">—</h3>
                </div>
            </div>
        </div>

        {{-- ACTUALES --}}
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Ingresos actuales (HNL)</h6>
                    <h3 id="totalHNLActual">—</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Ingresos actuales (USD)</h6>
                    <h3 id="totalUSDActual">—</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body p-2">
            <table class="table table-striped table-hover w-100 display" id="tablaCompras" style="width:100%">
                <thead>
                    <tr>
                        <th>Fecha creación</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Producto</th>
                        <th>Plan</th>
                        <th>Precio</th>
                        <th>Cant.</th>
                        <th>Estado</th>
                        <th>Marca</th>
                        <th>Canal</th>
                        <th>Tipo pago</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Usuario</th>
                        <th>Compra</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://nightly.datatables.net/responsive/js/dataTables.responsive.min.js"></script>
<script>
let dataTable = null;

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('filtrosCompras');

    form.addEventListener('submit', e => {
        e.preventDefault();
        cargarCompras();
    });

    document.getElementById('limpiarFiltros').addEventListener('click', () => {
        form.reset();
        cargarCompras();
    });

    document.querySelectorAll('.estado-checkbox')
        .forEach(cb => cb.addEventListener('change', syncEstados));

    document.getElementById('limpiarFiltros').addEventListener('click', () => {
        document.querySelectorAll('.estado-checkbox')
            .forEach(cb => cb.checked = false);
        document.getElementById('estadoHiddenInputs').innerHTML = '';
    });

    cargarCompras();
});

function syncEstados() {
    const container = document.getElementById('estadoHiddenInputs');
    container.innerHTML = '';

    document.querySelectorAll('.estado-checkbox:checked')
        .forEach(cb => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'estado[]';
            input.value = cb.value;
            container.appendChild(input);
        });
}

function cargarCompras() {
    const params = new URLSearchParams(new FormData(document.getElementById('filtrosCompras')));

    fetch(`{{ route('compras.data') }}?${params}`)
        .then(r => {
            if (!r.ok) {
                return r.text().then(text => {
                    const msg = text ? text.substring(0, 500) : `${r.status} ${r.statusText}`;
                    throw new Error(`Server error: ${msg}`);
                });
            }
            return r.json();
        })
        .then(response => {

            // =====================
            // HISTÓRICOS (NO CAMBIAN)
            // =====================
            document.getElementById('totalHNLHistorico').textContent =
                response.total_ingresos_historicos?.HNL
                    ?.toLocaleString("es-HN", { minimumFractionDigits: 2 }) ?? '—';

            document.getElementById('totalUSDHistorico').textContent =
                response.total_ingresos_historicos?.USD
                    ?.toLocaleString("en-US", { minimumFractionDigits: 2 }) ?? '—';

            // =====================
            // ACTUALES (CON FILTROS)
            // =====================
            document.getElementById('totalHNLActual').textContent =
                response.total_ingresos_actuales?.HNL
                    ?.toLocaleString("es-HN", { minimumFractionDigits: 2 }) ?? '—';

            document.getElementById('totalUSDActual').textContent =
                response.total_ingresos_actuales?.USD
                    ?.toLocaleString("en-US", { minimumFractionDigits: 2 }) ?? '—';

            renderTabla(response.data);
        })
        .catch(err => {
            console.error('Error cargando compras:', err);
            // limpiar UI y mostrar mensaje sencillo
            document.getElementById('totalHNLHistorico').textContent = '—';
            document.getElementById('totalUSDHistorico').textContent = '—';
            document.getElementById('totalHNLActual').textContent = '—';
            document.getElementById('totalUSDActual').textContent = '—';
            renderTabla([]);
            alert('Error cargando compras: ' + err.message);
        });
}

function renderTabla(data) {

    if (dataTable) {
        dataTable.destroy();
    }

    const tbody = document.querySelector('#tablaCompras tbody');
    tbody.innerHTML = data.map(row => `
        <tr>
            <td>${row.fechaCreacion ?? '—'}</td>
            <td>${row.nombreUsuario ?? '—'}</td>
            <td>${row.correo ?? '—'}</td>
            <td>${row.producto}</td>
            <td>${row.plan}</td>
            <td>${row.precio ?? '—'} ${row.moneda ?? ''}</td>
            <td>${row.cantidad}</td>
            <td>${row.estado}</td>
            <td>${row.marca}</td>
            <td>${row.canal}</td>
            <td>${row.tipoPago}</td>
            <td>${row.inicio ?? '—'}</td>
            <td>${row.fin ?? '—'}</td>
            <td>${row.idUsuario}</td>
            <td>${row.idCompra}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary">Ver</button>
            </td>
        </tr>
    `).join('');

    dataTable = $('#tablaCompras').DataTable({
    order: [[0, 'desc']],
    responsive: true,
    initComplete: function() {
		$(this.api().table().container()).find('input').attr('autocomplete', 'off');
	},
    language: { url: 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/es-MX.json' },
});

}
</script>
@endsection
