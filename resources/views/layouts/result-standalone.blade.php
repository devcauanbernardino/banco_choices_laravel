<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="light"
      data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Banco de Choices')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fluid-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/buttons-global.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/private-app.css') }}">
    <script>
        (function () {
            var saved = localStorage.getItem('bancochoices-theme') || 'light';
            if (saved === 'dark' || saved === 'light') {
                document.documentElement.setAttribute('data-theme', saved);
                document.documentElement.setAttribute('data-bs-theme', saved);
                try {
                    document.documentElement.style.colorScheme = saved;
                } catch (e) {}
            }
        })();
    </script>
    @stack('styles')
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
<body class="app-private-body result-standalone-body">
    <div class="result-standalone__toolbar">
        <a href="{{ route('dashboard') }}" class="result-standalone__back text-decoration-none">
            <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
            <span>{{ __('result.back_dashboard') }}</span>
        </a>
        <button type="button" class="result-standalone__theme js-theme-toggle-btn" aria-pressed="false" aria-label="{{ __('sidebar.appearance') }}">
            <span class="material-symbols-outlined result-standalone__theme-ico result-standalone__theme-ico--light" aria-hidden="true">light_mode</span>
            <span class="material-symbols-outlined result-standalone__theme-ico result-standalone__theme-ico--dark" aria-hidden="true">dark_mode</span>
        </button>
    </div>

    <main class="result-standalone__main" id="result-standalone-main">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    @stack('scripts')
</body>
</html>
