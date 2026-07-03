<div class="offcanvas offcanvas-end lp-offcanvas" tabindex="-1" id="lpOffcanvas" aria-labelledby="lpOffcanvasLabel">
    <div class="offcanvas-header">
        <a href="{{ route('home') }}" class="lp-offcanvas__brand" id="lpOffcanvasLabel">
            <span class="lp-offcanvas__brand-logo-box" aria-hidden="true">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" class="lp-offcanvas__brand-logo" width="40" height="40" decoding="async">
            </span>
            <span class="lp-offcanvas__brand-text">
                <span class="lp-offcanvas__brand-title">{{ __('index.page_title') }}</span>
                <span class="lp-offcanvas__brand-tagline">{{ __('sidebar.brand_tagline') }}</span>
            </span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="lp-offcanvas__nav" role="list">
            <li><a href="{{ route('home') }}"><i class="bi bi-house-door"></i> <span>{{ __('landing.offcanvas.inicio') }}</span></a></li>
            <li><a href="{{ route('home') }}#mascotes"><i class="bi bi-robot"></i> <span>{{ __('landing.topbar.mascotes') }}</span></a></li>
            <li><a href="{{ route('home') }}#funcionalidades"><i class="bi bi-list-check"></i> <span>{{ __('landing.topbar.funcionalidades') }}</span></a></li>
            <li><a href="{{ route('home') }}#modalidades"><i class="bi bi-mortarboard"></i> <span>{{ __('landing.offcanvas.simulacros') }}</span></a></li>
            <li><a href="{{ route('home') }}#planes"><i class="bi bi-tag"></i> <span>{{ __('landing.offcanvas.planes') }}</span></a></li>
            <li><a href="{{ route('home') }}#faq"><i class="bi bi-question-circle"></i> <span>{{ __('landing.offcanvas.ayuda') }}</span></a></li>
        </ul>

        <hr class="lp-offcanvas__sep">

        <a href="{{ route('demo.show') }}" class="btn lp-btn-primary lp-offcanvas__practicar">
            <i class="bi bi-play-circle"></i> {{ __('landing.offcanvas.practicar') }}
        </a>

        <div class="lp-offcanvas__cta">
            <a href="{{ route('login') }}" class="btn lp-btn-outline">
                {{ __('landing.offcanvas.login') }}
            </a>
            <a href="{{ route('signup.materias') }}" class="btn lp-btn-primary">
                {{ __('landing.offcanvas.signup') }}
            </a>
        </div>

        <hr class="lp-offcanvas__sep">

        <div class="lp-offcanvas__lang">
            <x-language-selector />
        </div>
    </div>
</div>
