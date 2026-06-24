@extends('layouts.app')

@section('content')
@php
$totalPages = ceil($total / 25);
@endphp

<div class="container">

    <h4 class="mb-3">Catálogo de normalización de ciudades</h4>
    
    <div id="saveMessage"
        class="alert alert-success d-none">
        Registro guardado.
    </div>
    
    <div class="row mb-3">

        <div class="col-md-4">
            <input type="text"
                   id="filterCanonica"
                   class="form-control form-control-sm"
                   value="{{ request('canonica') }}"
                   placeholder="Filtrar por ciudad canónica">
        </div>

        <div class="col-md-3">
            <select id="filterEstado" class="form-select form-select-sm">
                <option value="" {{ request('estado') === null ? 'selected' : '' }}>Todos</option>
                <option value="true" {{ request('estado') === 'true' ? 'selected' : '' }}>Activo</option>
                <option value="false" {{ request('estado') === 'false' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary w-100" id="btnFiltrar">
                Filtrar
            </button>
        </div>

    </div>

    {{-- ================= TABLA ================= --}}
    <div class="card shadow-sm">

        <div class="card-header">
            <strong>Alias de ciudades</strong>
        </div>

        <div class="card mb-4">

            <div class="card-header">
                Nuevo alias
            </div>

            <div class="card-body">

                <div class="row g-2">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            {{-- ================= FILA NUEVA ================= --}}
                            <thead class="table-dark">
                                <tr>
                                    <th>Alias</th>
                                    <th>Ciudad canónica</th>
                                    <th>País</th>
                                    <th>Estado</th>
                                    <th style="width:120px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-light">
                                    <td>
                                        <input type="text" class="form-control form-control-sm" id="new_alias">
                                    </td>

                                    <td>
                                        <input type="text" class="form-control form-control-sm" id="new_canonica">
                                        <datalist id="canonicasList"></datalist>
                                    </td>

                                    <td>
                                        <input type="text" class="form-control form-control-sm" id="new_pais">
                                    </td>

                                    <td>
                                        <select class="form-select" id="new_estado">
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    </td>

                                    <td>
                                        <button class="btn btn-success w-100" id="btnAdd">
                                            Agregar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered align-middle" id="cityTable">

                    <thead class="table-dark">
                        <tr>
                            <th>Alias</th>
                            <th>Ciudad canónica</th>
                            <th>País</th>
                            <th>Estado</th>
                            <th style="width:120px;">Acción</th>
                        </tr>
                    </thead>

                    <tbody>

                        {{-- ================= FILAS EXISTENTES ================= --}}
                        @foreach ($data as $row)
                        <tr data-alias="{{ $row['ciudad_alias'] }}">
                            {{-- Alias --}}
                            <td>
                                <span class="view-mode alias-text">
                                    {{ $row['ciudad_alias'] }}
                                </span>

                                <input type="text"
                                    class="form-control form-control-sm alias edit-mode d-none"
                                    value="{{ $row['ciudad_alias'] }}">
                            </td>

                            {{-- Canónica --}}
                            <td>
                                <span class="view-mode canonica-text">
                                    {{ $row['ciudad_canonica'] }}
                                </span>

                                <input type="text"
                                    class="form-control form-control-sm canonica edit-mode d-none"
                                    value="{{ $row['ciudad_canonica'] }}">
                            </td>

                            {{-- País --}}
                            <td>
                                <span class="view-mode pais-text">
                                    {{ $row['pais'] }}
                                </span>

                                <input type="text"
                                    class="form-control form-control-sm pais edit-mode d-none"
                                    value="{{ $row['pais'] }}">
                            </td>

                            {{-- Estado --}}
                            <td class="text-center">
                                <span class="view-mode estado-text">
                                    {{ $row['estado'] ? 'Activo' : 'Inactivo' }}
                                </span>

                                <input type="checkbox"
                                    class="form-check-input estado edit-mode d-none"
                                    {{ $row['estado'] ? 'checked' : '' }}>
                            </td>

                            {{-- Acciones --}}
                            <td>
                                <div class="view-mode">
                                    <button
                                        class="btn btn-sm btn-outline-primary btnEdit">
                                        Editar
                                    </button>
                                </div>

                                <div class="edit-mode d-none">
                                    <button
                                        class="btn btn-sm btn-success btnSave">
                                        Guardar
                                    </button>

                                    <button
                                        class="btn btn-sm btn-secondary btnCancel">
                                        Cancelar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">

                        @for($i = 1; $i <= $totalPages; $i++)
                            <li class="page-item {{ $page == $i ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $i }}&canonica={{ request('canonica') }}&estado={{ request('estado') }}">{{ $i }}</a>
                            </li>
                        @endfor

                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const csrf = "{{ csrf_token() }}";

    document.querySelectorAll('.btnEdit').forEach(btn => {
        btn.addEventListener('click', function () {
            let row = this.closest('tr');

            row.querySelectorAll('.view-mode').forEach(el => el.classList.add('d-none'));
            row.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('d-none'));
        });
    });

    document.querySelectorAll('.btnCancel').forEach(btn => {
        btn.addEventListener('click', function () {
            let row = this.closest('tr');
            let aliasText = row.querySelector('.alias-text').innerText.trim();
            let canonicaText = row.querySelector('.canonica-text').innerText.trim();
            let paisText = row.querySelector('.pais-text').innerText.trim();
            let estadoText = row.querySelector('.estado-text').innerText.trim();

            row.querySelector('.alias').value = aliasText;
            row.querySelector('.canonica').value = canonicaText;
            row.querySelector('.pais').value = paisText;
            row.querySelector('.estado').checked = estadoText === 'Activo';
            row.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
            row.querySelectorAll('.view-mode').forEach(el => el.classList.remove('d-none'));
        });

    });

    function showSuccess(message)
    {
        let alert = document.getElementById('saveMessage');

        alert.classList.remove('d-none', 'alert-danger');
        alert.classList.add('alert-success');

        alert.innerText = message;

        setTimeout(() => {
            alert.classList.add('d-none');
        }, 3000);
    }

    function showError(message)
    {
        let alert = document.getElementById('saveMessage');

        alert.classList.remove('d-none', 'alert-success');
        alert.classList.add('alert-danger');

        alert.innerText = message;

        setTimeout(() => {
            alert.classList.add('d-none');
        }, 5000);
    }

    document.querySelectorAll('.btnSave').forEach(btn => {
        btn.addEventListener('click', function () {

            let row = this.closest('tr');

            let originalAlias = row.dataset.alias;

            let data = {
                ciudad_alias: row.querySelector('.alias').value,
                ciudad_canonica: row.querySelector('.canonica').value,
                pais: row.querySelector('.pais').value,
                estado: row.querySelector('.estado').checked ? 1 : 0
            };

            fetch(`/cities/cities-alias/${encodeURIComponent(originalAlias)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    row.dataset.alias = data.ciudad_alias;
                    row.querySelector('.alias-text').innerText = data.ciudad_alias;
                    row.querySelector('.canonica-text').innerText = data.ciudad_canonica;
                    row.querySelector('.pais-text').innerText = data.pais;
                    row.querySelector('.estado-text').innerText = data.estado ? 'Activo' : 'Inactivo';
                    row.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
                    row.querySelectorAll('.view-mode').forEach(el => el.classList.remove('d-none'));

                    showSuccess('Registro actualizado');
                }
                else {
                    showError(res.message || 'Error');
                }
            });

        });
    });

    document.getElementById('btnAdd').addEventListener('click', function () {

        let btn = this;

        btn.disabled = true;

        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span>
            Guardando...
        `;

        let data = {
            ciudad_alias: document.getElementById('new_alias').value,
            ciudad_canonica: document.getElementById('new_canonica').value,
            pais: document.getElementById('new_pais').value,
            estado: document.getElementById('new_estado').value
        };

        fetch(`/cities/cities-alias`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {

            btn.disabled = false;
            btn.innerHTML = 'Agregar';

            if (res.ok) {

                document.getElementById('new_alias').value = '';
                document.getElementById('new_canonica').value = '';
                document.getElementById('new_pais').value = '';
                document.getElementById('new_estado').value = '1';

                showSuccess('Alias agregado correctamente');
                setTimeout(() => {window.location.reload();}, 1000);

            } else {

                showError(res.message || 'Error al guardar');
            }

        });

    });

    document.getElementById('btnFiltrar').addEventListener('click', function () {

        let canonica =
            document.getElementById('filterCanonica').value;

        let estado =
            document.getElementById('filterEstado').value;

        let url = new URL(window.location.href);

        url.searchParams.set('page', 1);

        if (canonica) {
            url.searchParams.set('canonica', canonica);
        } else {
            url.searchParams.delete('canonica');
        }

        if (estado !== '') {
            url.searchParams.set('estado', estado);
        } else {
            url.searchParams.delete('estado');
        }

        window.location.href = url.toString();
    });

    let timeout;

    /*document.getElementById('new_canonica').addEventListener('input', function () {

        clearTimeout(timeout);

        timeout = setTimeout(() => {

            fetch(`/cities/cities-canonicas?q=${this.value}`)
                .then(r => r.json())
                .then(data => {

                    let list = document.getElementById('canonicasList');
                    list.innerHTML = '';

                    data.forEach(item => {
                        let option = document.createElement('option');
                        option.value = item;
                        list.appendChild(option);
                    });

                });

        }, 250); // debounce
    });*/

    let aliasTimeout;

    /*document.querySelectorAll('.alias').forEach(input => {

        input.addEventListener('input', function () {

            let row = this.closest('tr');
            let original = row.dataset.alias;

            clearTimeout(aliasTimeout);

            aliasTimeout = setTimeout(() => {

                fetch(`/cities/cities-alias/check-duplicate?alias=${encodeURIComponent(this.value)}&original=${encodeURIComponent(original || '')}`)
                    .then(r => r.json())
                    .then(res => {

                        let msg = row.querySelector('.duplicate-msg');
                        let saveBtn = row.querySelector('.btnSave');

                        if (res.duplicate) {
                            msg.classList.remove('d-none');
                            saveBtn.disabled = true;
                            saveBtn.classList.add('btn-secondary');
                            saveBtn.classList.remove('btn-primary');
                            row.classList.add('table-danger');
                        } else {
                            msg.classList.add('d-none');
                            saveBtn.disabled = false;
                            saveBtn.classList.add('btn-primary');
                            saveBtn.classList.remove('btn-secondary');
                            row.classList.remove('table-danger');
                        }

                    });

            }, 300);

        });

    });*/

</script>
@endsection