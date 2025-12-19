<div class="card">
    <div class="card-body">
        <h5 class="card-title">Cambiar contraseña</h5>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Contraseña actual</label>
                <input type="password" class="form-control" name="current_password">
            </div>

            <div class="mb-3">
                <label class="form-label">Nueva contraseña</label>
                <input type="password" class="form-control" name="password">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" class="form-control" name="password_confirmation">
            </div>

            <button class="btn btn-primary">Actualizar</button>

            @if(session('status') === 'password-updated')
                <span class="text-success ms-3">Actualizada</span>
            @endif
        </form>
    </div>
</div>
