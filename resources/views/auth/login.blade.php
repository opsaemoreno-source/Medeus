@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-4">

        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4>Iniciar Sesión</h4>
            </div>

            <div class="card-body">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               required>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember">
                        <label class="form-check-label">Recordarme</label>
                    </div>

                    <button class="btn btn-primary w-100">Entrar</button>

                </form>

                <hr>

                <div class="text-center">
                    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
