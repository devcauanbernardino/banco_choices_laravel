<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="light"
      data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    {{-- Páginas públicas: tema claro fixo (paridade com PHP theme-head-public.php) --}}
    <script>
        (function () {
            try {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.documentElement.style.colorScheme = 'light';
            } catch (e) {}
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Banco de Choices')</title>

    {{-- Bootstrap 5.3.2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
<body@yield('body_attr')>@yield('content')<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>@stack('scripts')
</body>
</html>
