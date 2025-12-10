@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Editar Usuario</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}">
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}">
                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Nueva Contraseña (opcional)</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <button class="btn btn-primary">Actualizar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
