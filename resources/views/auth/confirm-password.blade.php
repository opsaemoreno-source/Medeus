@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-header">
            <h4 class="mb-0">Confirmar contraseña</h4>
        </div>

        <div class="card-body p-4">

            <p class="text-muted small">
                Esta es un área segura de la aplicación. Por favor confirma tu contraseña antes de continuar.
            </p>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button class="btn btn-primary w-100">Confirmar</button>

            </form>

        </div>
    </div>
</div>
@endsection
