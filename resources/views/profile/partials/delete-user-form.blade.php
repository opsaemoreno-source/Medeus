<div class="card border-danger">
    <div class="card-body">
        <h5 class="card-title text-danger">Eliminar cuenta</h5>

        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
            Eliminar cuenta
        </button>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
            @csrf
            @method('DELETE')

            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Esta acción es irreversible.</p>

                <input type="password" name="password" class="form-control" placeholder="Contraseña">
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
