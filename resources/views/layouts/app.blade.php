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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Material Icons + Symbols (painel / sidebar). display=block reduz texto-ligadura visível antes da fonte. --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fluid-layout.css') }}">
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

    {{-- Evita flash dos nomes dos glifos ("dashboard", …) antes das fontes de ícone carregarem --}}
    <script>
        (function () {
            function markIconsReady() {
                document.documentElement.classList.add('bc-icons-font-ready');
            }
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(markIconsReady);
            } else {
                markIconsReady();
            }
            window.addEventListener('load', markIconsReady);
            setTimeout(markIconsReady, 6000);
        })();
    </script>
</head>
<body class="app-private-body app-shell-painel">
    <div class="app-layout">
        {{-- Desktop sidebar --}}
        @include('components.sidebar')

        <div class="app-content-wrap">
            {{-- Tema | idioma | conta (perfil à direita) --}}
            <header class="app-content-topbar d-flex align-items-center gap-3">
                <div class="app-content-topbar-titles min-w-0 flex-grow-1">
                    <span class="app-content-topbar-heading fw-bold text-truncate d-lg-none d-block">@yield('mobile_title')</span>
                    @hasSection('topbar_title')
                        <span class="app-content-topbar-heading fw-semibold text-truncate d-none d-lg-block">@yield('topbar_title')</span>
                    @endif
                </div>
                <div class="app-content-topbar-actions d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="bc-topbar-icon-btn js-theme-toggle-btn position-relative"
                            aria-label="{{ __('sidebar.appearance') }}" aria-pressed="false">
                        <span class="material-symbols-outlined bc-topbar-btn-icon bc-topbar-btn-icon--light" aria-hidden="true">light_mode</span>
                        <span class="material-symbols-outlined bc-topbar-btn-icon bc-topbar-btn-icon--dark" aria-hidden="true">dark_mode</span>
                    </button>
                    <div class="app-content-topbar-lang">
                        @include('components.language-selector', ['compactTopbar' => true])
                    </div>
                    @include('components.topbar-account')
                </div>
            </header>

            {{-- Main content (título desktop integrado em cada página / sidebar) --}}
            <main class="app-main">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Modais ao nível do body: evita backdrop sobrepor o diálogo (stacking vs .app-main / sidebar). --}}
    @stack('modals')

    {{-- Bootstrap 5.3.2 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Theme & sidebar scripts --}}
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/sidebar-collapse.js') }}"></script>

    @stack('scripts')
</body>
</html>
