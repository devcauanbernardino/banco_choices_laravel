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
    {{-- Material Icons + Symbols (painel / sidebar) --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/buttons-global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app-shell-painel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-language-selector.css') }}">

    {{-- Theme + sidebar state (must run before first paint; sidebar key must match KEY in public/assets/js/sidebar-collapse.js) --}}
    <script>
        (function () {
            var saved = localStorage.getItem('bancochoices-theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            document.documentElement.setAttribute('data-bs-theme', saved);

            try {
                var sidebarKey = 'bancochoices-sidebar-collapsed';
                var collapsed = localStorage.getItem(sidebarKey);
                if (collapsed === null && localStorage.getItem('sidebar-collapsed') === '1') {
                    collapsed = '1';
                    localStorage.setItem(sidebarKey, '1');
                }
                if (collapsed === '1') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) { /* ignore */ }
        })();
    </script>

    @stack('styles')
</head>
<body class="app-private-body app-shell-painel">
    <div class="app-layout">
        {{-- Desktop sidebar --}}
        @include('components.sidebar')

        <div class="app-content-wrap">
            {{-- Mobile topbar --}}
            <header class="app-mobile-topbar d-lg-none justify-content-center">
                <span class="fw-bold">@yield('mobile_title')</span>
            </header>

            {{-- Main content (título desktop integrado em cada página / sidebar) --}}
            <main class="app-main p-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Bootstrap 5.3.2 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Theme & sidebar scripts --}}
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/sidebar-collapse.js') }}"></script>

    @stack('scripts')
</body>
</html>
