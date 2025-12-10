<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">

                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Registrarse</a>
                    </li>
                @else
                    <li class="nav-item">
                        <span class="nav-link">{{ Auth::user()->name }}</span>
                    </li>

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-danger btn-sm">Salir</button>
                        </form>
                    </li>
                @endguest

            </ul>
        </div>
    </div>
</nav>

<main class="container">
    @yield('content')
</main>

<!-- Scripts de cada vista -->
@yield('scripts')
</body>
</html>
