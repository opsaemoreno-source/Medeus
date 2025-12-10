@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4>Verifica tu correo</h4>
            </div>

            <div class="card-body text-center">

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
</div>
@endsection
