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
    <link rel="stylesheet" href="{{ asset('assets/css/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/buttons-global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-language-selector.css') }}">

    {{-- Theme initialization (must run before first paint) --}}
    <script>
        (function () {
            var saved = localStorage.getItem('bancochoices-theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            document.documentElement.setAttribute('data-bs-theme', saved);

            if (localStorage.getItem('sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    @stack('styles')
</head>
<body class="app-private-body">
    <div class="app-layout">
        {{-- Desktop sidebar --}}
        @include('components.sidebar')

        <div class="app-content-wrap">
            {{-- Mobile topbar --}}
            <header class="app-mobile-topbar d-lg-none justify-content-center">
                <span class="fw-bold">@yield('mobile_title')</span>
            </header>

            {{-- Desktop topbar: título vem de @section('topbar_title') em cada página --}}
            @php
                $topbarTitleResolved = trim($__env->yieldContent('topbar_title', ''));
            @endphp
            @include('components.topbar', [
                'topbarTitle' => $topbarTitleResolved !== '' ? $topbarTitleResolved : __('nav.dashboard'),
            ])

            {{-- Main content --}}
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
