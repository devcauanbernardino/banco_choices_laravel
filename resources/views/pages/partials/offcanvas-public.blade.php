<div class="offcanvas offcanvas-end lp-offcanvas" tabindex="-1" id="lpOffcanvas" aria-labelledby="lpOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="lpOffcanvasLabel">{{ config('branding.brand_name', 'Banco de Choices') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="lp-offcanvas__nav" role="list">
            <li><a href="{{ route('home') }}"><i class="bi bi-house-door"></i> <span>{{ __('landing.offcanvas.inicio') }}</span></a></li>
            <li><a href="{{ route('home') }}#modalidades"><i class="bi bi-mortarboard"></i> <span>{{ __('landing.offcanvas.simulacros') }}</span></a></li>
            <li><a href="{{ route('home') }}#como-funciona"><i class="bi bi-list-check"></i> <span>{{ __('landing.topbar.como_funciona') }}</span></a></li>
            <li><a href="{{ route('home') }}#planes"><i class="bi bi-tag"></i> <span>{{ __('landing.offcanvas.planes') }}</span></a></li>
            <li><a href="{{ route('home') }}#faq"><i class="bi bi-question-circle"></i> <span>{{ __('landing.offcanvas.ayuda') }}</span></a></li>
            <li><a href="{{ route('demo.show') }}"><i class="bi bi-play-circle"></i> <span>{{ __('landing.offcanvas.practicar') }}</span></a></li>
        </ul>

        <hr class="lp-offcanvas__sep">

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
