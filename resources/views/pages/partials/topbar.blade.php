<nav class="lp-topbar" id="lpTopbar" aria-label="Topo">
    <div class="lp-topbar__inner">
        <a href="{{ route('home') }}" class="lp-topbar__brand" id="lpBrandLink" aria-label="Banco de Choices">
            <img src="{{ \App\Support\Branding::faviconUrl() }}" alt="Banco de Choices" class="lp-topbar__logo" width="40" height="40">
        </a>

        <ul class="lp-topbar__nav d-none d-lg-flex" role="list">
            <li><a href="{{ route('home') }}#funcionalidades">{{ __('landing.topbar.funcionalidades') }}</a></li>
            <li><a href="{{ route('home') }}#modalidades">{{ __('landing.topbar.modalidades') }}</a></li>
            <li><a href="{{ route('home') }}#planes">{{ __('landing.topbar.planes') }}</a></li>
        </ul>

        <div class="lp-topbar__actions">
            <div class="lp-topbar__lang d-none d-md-block">
                <x-language-selector :compactTopbar="true" />
            </div>
            <a href="{{ route('login') }}" class="btn lp-btn-outline d-none d-md-inline-flex">
                {{ __('landing.topbar.login') }}
            </a>
            <a href="{{ route('demo.show') }}" class="btn lp-btn-primary">
                {{ __('landing.topbar.cta') }}
            </a>
            <button class="lp-topbar__burger d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#lpOffcanvas"
                    aria-controls="lpOffcanvas" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</nav>
