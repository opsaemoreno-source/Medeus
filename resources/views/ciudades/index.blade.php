@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Normalización de Ciudades</h3>

    <div class="mb-3">
        <label>Ciudad Canónica</label>
        <select id="ciudadCanonica" class="form-control"></select>
    </div>

    <div class="mb-3">
        <label>País</label>
        <input type="text" id="pais" class="form-control" readonly>
    </div>

    <table class="table table-sm" id="tablaAlias">
        <thead>
            <tr>
                <th>Alias</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="mb-3">
        <button class="btn btn-success" id="btnNuevaCanonica">Agregar Ciudad Canónica</button>
        <button class="btn btn-primary" id="btnNuevoAlias">Agregar Alias</button>
        <button class="btn btn-secondary" id="btnEditarPais">Editar País</button>
    </div>
</div>

<!-- Modal para Alias -->
<div class="modal fade" id="aliasModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Alias de Ciudad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAlias">
          <div class="mb-3">
            <label>Alias</label>
            <input type="text" class="form-control" id="inputAlias" required>
          </div>
          <div class="mb-3">
            <label>Prioridad</label>
            <input type="number" class="form-control" id="inputPrioridad" value="0">
          </div>
          <input type="hidden" id="aliasOriginal">
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarAlias">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal Ciudad Canónica -->
<div class="modal fade" id="canonicaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Ciudad Canónica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCanonica">
          <div class="mb-3">
            <label>Ciudad Canónica</label>
            <input type="text" class="form-control" id="inputCanonica" required>
          </div>
          <div class="mb-3">
            <label>Alias Inicial</label>
            <input type="text" class="form-control" id="inputAliasCanonica" required>
            </div>
          <div class="mb-3">
            <label>País</label>
            <input type="text" class="form-control" id="inputPais" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" id="btnGuardarCanonica">Guardar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="paisModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar País</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control" id="inputEditarPais">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarPais">Guardar</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let aliasModal = new bootstrap.Modal(document.getElementById('aliasModal'));
    let canonicaModal = new bootstrap.Modal(document.getElementById('canonicaModal'));
    let paisModal = new bootstrap.Modal(document.getElementById('paisModal'));

    fetch('/ciudades/canonicas')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('ciudadCanonica');
            const paisInput = document.getElementById('pais');

            // Limpiar completamente
            select.innerHTML = '';

            // Placeholder
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Seleccione una opción';
            placeholder.disabled = true;
            placeholder.selected = true;
            select.appendChild(placeholder);

            // Opciones reales
            data.forEach(c => {
                const option = document.createElement('option');
                option.value = c.ciudad_canonica;
                option.textContent = c.ciudad_canonica;
                option.dataset.pais = c.pais;
                select.appendChild(option);
            });

            // Forzar estado inicial
            select.value = '';
            paisInput.value = '';
        });


    document.getElementById('ciudadCanonica').addEventListener('change', function () {
        if (!this.value) return;

        const pais = this.options[this.selectedIndex].dataset.pais;
        document.getElementById('pais').value = pais;
        cargarAlias(this.value);
    });

    function cargarAlias(canonica) {
        if (!canonica) return; // evita GET a /ciudades/alias sin parámetro

        fetch(`/ciudades/alias/${encodeURIComponent(canonica)}`) // <-- ruta correcta
            .then(r => r.json())
            .then(data => {
                const tbody = document.querySelector('#tablaAlias tbody');
                tbody.innerHTML = '';

                data.forEach(a => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${a.ciudad_alias}</td>
                        <td>${a.prioridad ?? 0}</td>
                        <td>${a.estado ? 'Activo' : 'Inactivo'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm editar" 
                                    data-alias="${a.ciudad_alias}" 
                                    data-prioridad="${a.prioridad ?? 0}">Editar</button>
                            <button class="btn btn-danger btn-sm eliminar" 
                                    data-alias="${a.ciudad_alias}">Eliminar</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('eliminar')) {
            if (!confirm('¿Desactivar alias?')) return;

            fetch(`/ciudades/alias/${encodeURIComponent(e.target.dataset.alias)}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                document.getElementById('ciudadCanonica').dispatchEvent(new Event('change'));
            });
        }

        if (e.target.classList.contains('editar')) {
            document.getElementById('inputAlias').value = e.target.dataset.alias;
            document.getElementById('inputPrioridad').value = e.target.dataset.prioridad || 0;
            document.getElementById('aliasOriginal').value = e.target.dataset.alias;

            aliasModal.show();
        }
    });

    document.getElementById('btnNuevoAlias').addEventListener('click', function() {
        let canonica = document.getElementById('ciudadCanonica').value;
        if (!canonica) return alert('Seleccione una ciudad canónica');
        document.getElementById('inputAlias').value = '';
        document.getElementById('inputPrioridad').value = 0;
        document.getElementById('aliasOriginal').value = '';
        aliasModal.show();
    });

    // Editar país de ciudad canónica
    document.getElementById('btnEditarPais').addEventListener('click', () => {
        const select = document.getElementById('ciudadCanonica');
        if (!select.value) return alert('Seleccione una ciudad canónica');

        document.getElementById('inputEditarPais').value =
            select.options[select.selectedIndex].dataset.pais;

        paisModal.show();
    });

    document.getElementById('btnGuardarPais').addEventListener('click', () => {
        const select = document.getElementById('ciudadCanonica');
        const nuevoPais = document.getElementById('inputEditarPais').value.trim();
        if (!nuevoPais) return;

        fetch('/ciudades/canonicas', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ciudad_canonica: select.value,
                pais: nuevoPais
            })
        }).then(() => {
            select.options[select.selectedIndex].dataset.pais = nuevoPais;
            document.getElementById('pais').value = nuevoPais;
            paisModal.hide();
        });
    });

    document.getElementById('btnGuardarAlias').addEventListener('click', function() {
        const canonica = document.getElementById('ciudadCanonica').value;
        if (!canonica) return alert('Seleccione una ciudad canónica');

        const aliasOriginal = document.getElementById('aliasOriginal').value;
        const alias = document.getElementById('inputAlias').value;
        const prioridad = document.getElementById('inputPrioridad').value || 0;

        const method = aliasOriginal ? 'PUT' : 'POST';

        fetch('/ciudades/alias', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                alias_original: aliasOriginal,
                ciudad_alias: alias,
                ciudad_canonica: canonica,
                prioridad: prioridad
            })
        }).then(() => {
            aliasModal.hide();
            cargarAlias(canonica);
        });
    });

    // Abrir modal
    document.getElementById('btnNuevaCanonica').addEventListener('click', function() {
        document.getElementById('inputCanonica').value = '';
        document.getElementById('inputPais').value = '';
        canonicaModal.show();
    });

    // Guardar ciudad canónica
    document.getElementById('btnGuardarCanonica').addEventListener('click', function() {
        const canonica = document.getElementById('inputCanonica').value.trim();
        const pais = document.getElementById('inputPais').value.trim();
        if (!canonica || !pais) return alert('Complete todos los campos');

        fetch('/ciudades/canonicas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ciudad_canonica: canonica,
                ciudad_alias: document.getElementById('inputAliasCanonica').value.trim(),
                pais: pais,
                prioridad: 1
            })
        }).then(r => r.json())
        .then(() => {
            canonicaModal.hide();
            location.reload(); // recargar para que aparezca en select
        });
    });


});

</script>


@endsection

