@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-header">
            <h4 class="mb-0">Nueva contraseña</h4>
        </div>

        <div class="card-body p-4">

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $request->email) }}"
                           required>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100">Guardar</button>

            </form>

        </div>
    </div>
</div>
@endsection
