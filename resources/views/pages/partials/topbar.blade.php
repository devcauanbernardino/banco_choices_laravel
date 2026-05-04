<nav class="lp-topbar" id="lpTopbar" aria-label="Topo">
    <div class="lp-topbar__inner">
        <a href="{{ route('home') }}" class="lp-topbar__brand" id="lpBrandLink" aria-label="Banco de Choices">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="Banco de Choices" class="lp-topbar__logo" width="40" height="40">
        </a>

        <div class="lp-topbar__actions">
            <a href="{{ route('login') }}" class="btn lp-btn-outline d-none d-md-inline-flex">
                {{ __('landing.topbar.login') }}
            </a>
            <a href="{{ route('demo.show') }}" class="btn lp-btn-primary">
                {{ __('landing.topbar.cta') }}
            </a>
            <button class="lp-topbar__burger" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#lpOffcanvas"
                    aria-controls="lpOffcanvas" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</nav>
