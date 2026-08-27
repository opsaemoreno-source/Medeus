@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-header">
            <h4 class="mb-0">Recuperar contraseña</h4>
        </div>

        <div class="card-body p-4">

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button class="btn btn-primary w-100">
                    Enviar enlace
                </button>

            </form>

        </div>
    </div>
</div>
@endsection
