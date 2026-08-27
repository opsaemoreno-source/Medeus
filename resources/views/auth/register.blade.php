@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-header">
            <h4 class="mb-0">Registro</h4>
        </div>

        <div class="card-body p-4">

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="name"
                           value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <button class="btn btn-primary w-100">Registrarse</button>
            </form>

        </div>
    </div>
</div>
@endsection
