@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    <h4 class="mb-3">Compras</h4>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="filtrosCompras" class="row g-3">

                {{-- Estado --}}
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="ACTIVE_PENDING">ACTIVE PENDING</option>
                        <option value="CANCELLED">CANCELLED</option>
                        <option value="CANCEL_PENDING">CANCEL PENDING</option>
                        <option value="INCOMPLETE">INCOMPLETE</option>
                    </select>
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

                <div class="col-md-2">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" name="fecha_fin" class="form-control">
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total ingresos (HNL)</h6>
                    <h3 id="totalHNL">—</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total ingresos (USD)</h6>
                    <h3 id="totalUSD">—</h3>
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
                        <th>Usuario</th>
                        <th>Compra</th>
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

    cargarCompras();
});

function cargarCompras() {
    const params = new URLSearchParams(new FormData(document.getElementById('filtrosCompras')));

    fetch(`{{ route('compras.data') }}?${params}`)
        .then(r => r.json())
        .then(response => {

            document.getElementById('totalHNL').textContent =
                response.total_ingresos?.HNL?.toLocaleString("es-HN", { minimumFractionDigits: 2 }) ?? '—';

            document.getElementById('totalUSD').textContent =
                response.total_ingresos?.USD?.toLocaleString("en-US", { minimumFractionDigits: 2 }) ?? '—';

            renderTabla(response.data);
        });
}

function renderTabla(data) {

    if (dataTable) {
        dataTable.destroy();
    }

    const tbody = document.querySelector('#tablaCompras tbody');
    tbody.innerHTML = data.map(row => `
        <tr>
            <td>${row.idUsuario}</td>
            <td>${row.idCompra}</td>
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
            <td>
                <button class="btn btn-sm btn-outline-primary">Ver</button>
            </td>
        </tr>
    `).join('');

    dataTable = $('#tablaCompras').DataTable({
    order: [[0, 'asc']],
    responsive: true,
    initComplete: function() {
		$(this.api().table().container()).find('input').attr('autocomplete', 'off');
	},
    language: { url: 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/es-MX.json' },
});

}
</script>
@endsection
