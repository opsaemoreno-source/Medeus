<div class="card">
    <div class="card-body">
        <h5 class="card-title">Información del perfil</h5>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input class="form-control" name="name" value="{{ old('name', $user->name) }}">
                @error('name') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" name="email" value="{{ old('email', $user->email) }}" disabled readonly>
                @error('email') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <button class="btn btn-primary">Guardar</button>

            @if(session('status') === 'profile-updated')
                <span class="text-success ms-3">Guardado</span>
            @endif
        </form>
    </div>
</div>
