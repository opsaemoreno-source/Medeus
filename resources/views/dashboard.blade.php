@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-4">

        <!-- Tarjeta: Encuestas -->
        <div class="col-md-4">
            <a href="{{ route('encuestas.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #198754; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Encuestas</h3>
                        <p class="mb-0">Ver listado de encuestas ingresadas</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta: Suscriptores -->
        <div class="col-md-4">
            <a href="{{ route('suscriptores.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #0d6efd; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Suscriptores</h3>
                        <p class="mb-0">Ver listado de suscriptores registrados</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta: Estadísticas -->
        <div class="col-md-4">
            <a href="{{ route('suscriptores.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #6f42c1; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Estadísticas</h3>
                        <p class="mb-0">Ver estadísticas en base a los datos existentes</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta: Usuarios -->
        <div class="col-md-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #fd7e14; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Usuarios</h3>
                        <p class="mb-0">Ver listado de usuarios registrados</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>
@endsection
