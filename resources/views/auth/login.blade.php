@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="card auth-card">
        <div class="card-header border-0">
            <div class="d-flex justify-content-center mb-2">
                <img src="{{ asset('images/logo2.png') }}" alt="MEDEUS" class="img-fluid" style="max-height: 90px;">
            </div>
            <h4 class="mb-0">Iniciar Sesión</h4>
        </div>

        <div class="card-body p-4">

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar</button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('password.request') }}" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
            </div>

        </div>
    </div>
</div>
@endsection
