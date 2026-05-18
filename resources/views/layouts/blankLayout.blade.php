<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CedarGov') | {{ config('variables.templateName', 'CedarGov') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    {{-- Design System --}}
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}" rel="stylesheet">

    @yield('vendor-style')
    @yield('page-style')
    @stack('styles')
</head>
<body class="es-auth-body">

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

