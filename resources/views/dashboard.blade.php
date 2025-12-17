@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-4">

        <!-- Tarjeta: Encuestas -->
        <div class="col-md-4">
            <a href="{{ route('encuestas.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #65cea7; border-radius: 12px;">
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
                <div class="card text-white shadow-sm h-100" style="background-color: #6bafbd; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Suscriptores</h3>
                        <p class="mb-0">Ver listado de suscriptores registrados</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta: Compras -->
        <div class="col-md-4">
            <a href="{{ route('compras.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #f3ce85; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Compras</h3>
                        <p class="mb-0">Ver registro de compras efectuadas</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta: Estadísticas -->
        <div class="col-md-4">
            <a href="{{ route('estadisticas.index') }}" class="text-decoration-none">
                <div class="card text-white shadow-sm h-100" style="background-color: #fc8675; border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Estadísticas</h3>
                        <p class="mb-0">Ver estadísticas en base a los datos existentes</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta: Usuarios -->
        @if(Auth::user() && Auth::user()->is_admin == 1)
        <div class="col-md-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100" style="background-color: #F1F5FC; border-radius: 12px; color: #555;">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <h3 class="fw-bold mb-2">Usuarios</h3>
                        <p class="mb-0">Ver listado de usuarios registrados</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

    </div>
</div>
@endsection
