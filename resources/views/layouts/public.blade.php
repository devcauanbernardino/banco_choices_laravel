<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="@yield('public_data_theme', 'light')"
      data-bs-theme="@yield('public_data_theme', 'light')">
<head>
    <meta charset="UTF-8">
    {{-- Permite que páginas públicas optem por dark (landing nova) ou light (auth, checkout, etc.) --}}
    <script>
        (function () {
            try {
                var theme = document.documentElement.getAttribute('data-theme') || 'light';
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
                document.documentElement.style.colorScheme = theme;
            } catch (e) {}
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Banco de Choices')</title>

    {{-- Bootstrap 5.3.2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Material Icons (áreas que usam ícone) --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    {{-- Custom CSS — mesma ordem de prioridade que o projeto PHP (tokens → botões → app → idioma) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fluid-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/buttons-global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-language-selector.css') }}">

    @stack('styles')
</head>
<body@yield('body_attr')>
    @hasSection('public_topbar')
        @yield('public_topbar')
    @endif

    @yield('content')

    @hasSection('public_footer')
        @yield('public_footer')
    @endif

    @hasSection('public_offcanvas')
        @yield('public_offcanvas')
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
