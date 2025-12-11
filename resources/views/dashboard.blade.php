@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Dashboard</h2>

    <div class="row">
        <!-- Tarjeta: Encuestas -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('encuestas.index') }}" class="text-decoration-none">
                <div class="card text-white" style="background-color: #6ca8a8; border: none; border-radius: 12px;">
                    <div class="card-body d-flex flex-column align-items-start p-4">
                        <h3 class="fw-bold mb-1">Encuestas</h3>
                        <p class="mb-0">Ver listado de encuestas ingresadas</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="{{ route('suscriptores.index') }}" class="text-decoration-none">
                <div class="card text-white" style="background-color: #28a745; border: none; border-radius: 12px;">
                    <div class="card-body d-flex flex-column align-items-start p-4">
                        <h3 class="fw-bold mb-1">Suscriptores</h3>
                        <p class="mb-0">Ver listado de suscriptores registrados</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card text-white" style="background-color: #ffc107; border: none; border-radius: 12px;">
                    <div class="card-body d-flex flex-column align-items-start p-4">
                        <h3 class="fw-bold mb-1">Usuarios</h3>
                        <p class="mb-0">Ver listado de usuarios registrados</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
