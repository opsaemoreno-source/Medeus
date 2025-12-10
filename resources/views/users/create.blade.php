@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Nuevo Usuario</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}">
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}">
                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <button class="btn btn-success">Guardar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
