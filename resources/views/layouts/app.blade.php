<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pagina') — {{ config('app.name', 'Sondaggi') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
@php
    $isDashboard = str_starts_with(request()->path(), 'dashboard');
@endphp
<nav class="navbar navbar-expand-lg navbar-light site-navbar sticky-top" id="site-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">{{ config('app.name', 'SondaggiModerni') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
                aria-controls="topNav" aria-expanded="false" aria-label="Apri menu di navigazione">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('home') ? ' active' : '' }}" href="{{ route('home') }}"@if(request()->routeIs('home')) aria-current="page"@endif>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('surveys.public.index') ? ' active' : '' }}" href="{{ route('surveys.public.index') }}"@if(request()->routeIs('surveys.public.index')) aria-current="page"@endif>Sondaggi pubblici</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('about') ? ' active' : '' }}" href="{{ route('about') }}"@if(request()->routeIs('about')) aria-current="page"@endif>Chi siamo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('contacts.index', 'contacts.submit') ? ' active' : '' }}" href="{{ route('contacts.index') }}"@if(request()->routeIs('contacts.index', 'contacts.submit')) aria-current="page"@endif>Contatti</a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link{{ $isDashboard ? ' active' : '' }}" href="{{ route('dashboard') }}"@if($isDashboard) aria-current="page"@endif>Dashboard</a>
                    </li>
                @endauth
            </ul>
            <div class="d-flex gap-2">
                @auth
                    <form method="post" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button class="btn btn-outline-secondary" type="submit">Logout</button>
                    </form>
                @else
                    <a class="btn btn-outline-secondary" href="{{ route('login') }}">Login</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Registrati</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="container py-4 flex-grow-1">
    @yield('content')
</main>

<footer class="site-footer mt-auto">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <span class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'SondaggiModerni') }}</span>
            <nav aria-label="Footer">
                <a class="me-3" href="{{ route('about') }}">Chi siamo</a>
                <a class="me-3" href="{{ route('contacts.index') }}">Contatti</a>
                <a href="{{ route('home') }}">Home</a>
            </nav>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
