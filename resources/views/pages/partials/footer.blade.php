@php
    $whatsapp = config('landing.footer.social.whatsapp');
    $instagram = config('landing.footer.social.instagram');
    $brandName = config('branding.brand_name', 'Banco de Choices');
@endphp
<footer class="lp-footer">
    <div class="lp-footer__inner">
        <div class="lp-footer__col lp-footer__brand">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ $brandName }}" class="lp-footer__logo" width="48" height="48">
            <ul class="lp-footer__social" role="list">
                @if($whatsapp)
                    <li><a href="{{ $whatsapp }}" aria-label="WhatsApp" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i></a></li>
                @endif
                @if($instagram)
                    <li><a href="{{ $instagram }}" aria-label="Instagram" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a></li>
                @endif
            </ul>
        </div>

        <div class="lp-footer__col">
            <h6 class="lp-footer__title">{{ __('landing.footer.nav_title') }}</h6>
            <ul class="lp-footer__links" role="list">
                <li><a href="{{ route('home') }}">{{ __('landing.footer.nav.inicio') }}</a></li>
                <li><a href="{{ route('home') }}#planes">{{ __('landing.footer.nav.planes') }}</a></li>
                <li><a href="{{ route('demo.show') }}">{{ __('landing.footer.nav.practicar') }}</a></li>
                <li><a href="{{ route('home') }}#modalidades">{{ __('landing.footer.nav.simulacros') }}</a></li>
                <li><a href="{{ route('home') }}#como-funciona">{{ __('landing.footer.nav.referidos') }}</a></li>
                <li><a href="{{ route('home') }}#faq">{{ __('landing.footer.nav.ayuda') }}</a></li>
            </ul>
        </div>

        <div class="lp-footer__col lp-footer__col--objetivo">
            <h6 class="lp-footer__title">{{ __('landing.footer.objetivo_title') }}</h6>
            <p class="lp-footer__objetivo">{{ __('landing.footer.objetivo') }}</p>
        </div>

        <div class="lp-footer__col">
            <h6 class="lp-footer__title">{{ __('landing.footer.contact_title') }}</h6>
            <ul class="lp-footer__links" role="list">
                <li><a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a></li>
            </ul>
        </div>
    </div>

    <div class="lp-footer__bottom">
        <span>© {{ date('Y') }} {{ __('landing.footer.copyright') }}</span>
    </div>
</footer>
