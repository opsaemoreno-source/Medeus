@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <div class="card auth-card" style="max-width: 480px;">
        <div class="card-header">
            <h4 class="mb-0">Verifica tu correo</h4>
        </div>

        <div class="card-body text-center p-4">

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    Se envió un nuevo enlace de verificación.
                </div>
            @endif

            <p>
                Antes de continuar, revisa tu correo para encontrar el enlace de verificación.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn btn-primary">Reenviar enlace</button>
            </form>

        </div>
    </div>
</div>
@endsection
