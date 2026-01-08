<div>
    <h3 class="mb-4">Estadísticas de Compras</h3>
    <div class="btn-group mb-3">
        <button class="btn btn-outline-primary active" data-modo="cantidad">
            Cantidad
        </button>
        <button class="btn btn-outline-primary" data-modo="valor">
            Valor
        </button>
    </div>
    
    <ul class="nav nav-tabs mb-3" id="tabsMoneda">
        <li class="nav-item">
            <button class="nav-link active" data-moneda="HNL">Lempiras</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-moneda="USD">Dólares</button>
        </li>
    </ul>

    <div class="row g-4">
        <div class="col-md-6"><canvas id="comprasDia"></canvas></div>
        <div class="col-md-6"><canvas id="comprasProducto"></canvas></div>
        <div class="col-md-6"><canvas id="comprasEstado"></canvas></div>
        <div class="col-md-6"><canvas id="comprasMarca"></canvas></div>
        <div class="col-md-6"><canvas id="comprasCanal"></canvas></div>
        <div class="col-md-6"><canvas id="comprasFrecuencia"></canvas></div>
    </div>
</div>

