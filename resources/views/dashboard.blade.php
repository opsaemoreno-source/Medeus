@extends('layouts.app')

@section('content')
<div class="page-heading">
    <h2>Dashboard</h2>
</div>

<div class="row g-4">

    <!-- Tarjeta: Encuestas -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('encuestas.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--encuestas h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-clipboard-data module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Encuestas</h3>
                    <p class="mb-0">Ver listado de encuestas ingresadas</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta: Suscriptores -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('suscriptores.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--suscriptores h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-people module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Usuarios</h3>
                    <p class="mb-0">Ver listado de usuarios registrados</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta: Compras -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('compras.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--compras h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-bag-check module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Compras</h3>
                    <p class="mb-0">Ver registro de compras efectuadas</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta: Estadísticas -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('estadisticas.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--estadisticas h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-bar-chart-line module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Estadísticas</h3>
                    <p class="mb-0">Ver estadísticas en base a los datos existentes</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta: Chatbot -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('chatbot.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--chatbot h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-robot module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Chatbot</h3>
                    <p class="mb-0">Administrar temas y configuraciones</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta: Conversaciones -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('chatbot.conversations.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--conversaciones h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-chat-dots module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Conversaciones</h3>
                    <p class="mb-0">Historial de conversaciones del chatbot.</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta: Ciudades -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('cities.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--ciudades h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-geo-alt module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Ciudades</h3>
                    <p class="mb-0">Catálogo de equivalencia de ciudades y comunidades.</p>
                </div>
            </div>
        </a>
    </div>

    @if(Auth::user() && Auth::user()->is_admin == 1)
    <!-- Tarjeta: Usuarios del sistema -->
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('users.index') }}" class="text-decoration-none">
            <div class="card module-card module-card--usuarios h-100">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <i class="bi bi-person-gear module-icon mb-2"></i>
                    <h3 class="fw-bold mb-2">Usuarios</h3>
                    <p class="mb-0">Ver listado de usuarios del sistema.</p>
                </div>
            </div>
        </a>
    </div>
    @endif

</div>
@endsection
