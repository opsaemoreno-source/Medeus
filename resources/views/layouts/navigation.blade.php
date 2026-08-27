<div class="sidebar-brand">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="sidebar-brand-logo">
    <span>MEDEUS</span>
</div>

<nav class="nav flex-column flex-grow-1">
    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <p class="sidebar-section-title">Datos</p>
    <a class="nav-link {{ request()->routeIs('encuestas.*') ? 'active' : '' }}" href="{{ route('encuestas.index') }}">
        <i class="bi bi-clipboard-data"></i> Encuestas
    </a>
    <a class="nav-link {{ request()->routeIs('suscriptores.*') ? 'active' : '' }}" href="{{ route('suscriptores.index') }}">
        <i class="bi bi-people"></i> Usuarios App
    </a>
    <a class="nav-link {{ request()->routeIs('compras.*') ? 'active' : '' }}" href="{{ route('compras.index') }}">
        <i class="bi bi-bag-check"></i> Compras
    </a>
    <a class="nav-link {{ request()->routeIs('estadisticas.*') ? 'active' : '' }}" href="{{ route('estadisticas.index') }}">
        <i class="bi bi-bar-chart-line"></i> Estadísticas
    </a>
    <a class="nav-link {{ request()->routeIs('cities.*') ? 'active' : '' }}" href="{{ route('cities.index') }}">
        <i class="bi bi-geo-alt"></i> Ciudades
    </a>

    <p class="sidebar-section-title">Chatbot</p>
    <a class="nav-link {{ request()->routeIs('chatbot.index') || request()->routeIs('chatbot.create') || request()->routeIs('chatbot.edit') || request()->routeIs('chatbot.versions') ? 'active' : '' }}" href="{{ route('chatbot.index') }}">
        <i class="bi bi-robot"></i> Temas
    </a>
    <a class="nav-link {{ request()->routeIs('chatbot.conversations.*') ? 'active' : '' }}" href="{{ route('chatbot.conversations.index') }}">
        <i class="bi bi-chat-dots"></i> Conversaciones
    </a>

    @if(Auth::user() && Auth::user()->is_admin == 1)
        <p class="sidebar-section-title">Administración</p>
        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
            <i class="bi bi-person-gear"></i> Usuarios del sistema
        </a>
    @endif
</nav>

<div class="sidebar-footer">
    &copy; {{ date('Y') }} Grupo OPSA
</div>
