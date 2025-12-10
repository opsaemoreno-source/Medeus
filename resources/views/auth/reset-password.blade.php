@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-4">

        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4>Nueva contraseña</h4>
            </div>

            <div class="card-body">

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

                    <button class="btn btn-success w-100">Guardar</button>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection
