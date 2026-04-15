<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="light"
      data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Banco de Choices')</title>

    {{-- Bootstrap 5.3.2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Material Icons --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme-app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public-language-selector.css') }}">

    {{-- Theme initialization (must run before first paint) --}}
    <script>
        (function () {
            var saved = localStorage.getItem('bancochoices-theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            document.documentElement.setAttribute('data-bs-theme', saved);
        })();
    </script>

    @stack('styles')
</head>
<body>
    @yield('content')

    {{-- Bootstrap 5.3.2 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
