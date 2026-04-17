{{-- Rodapé padrão das páginas auth (login, recuperar, redefinir). --}}
<footer class="auth-login-footer">
    <div class="auth-login-footer-inner">
        <div class="row gx-4 gy-3 auth-login-footer-row">
            <div class="col-md-4 d-flex flex-column justify-content-center">
                <a href="{{ route('home') }}" class="auth-login-footer-logo-link d-inline-block mb-2" aria-label="{{ __('index.page_title') }}">
                    <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" class="auth-login-footer-logo" width="200" height="56" decoding="async">
                </a>
                <p class="small text-secondary mb-0">{{ __('login.footer_tagline') }}</p>
            </div>
            <div class="col-md-5 d-flex flex-column justify-content-center">
                <nav class="d-flex flex-wrap gap-3 justify-content-md-center" aria-label="Legal">
                    <a href="{{ route('home') }}#privacidad">{{ __('login.footer_privacy') }}</a>
                    <a href="{{ route('home') }}#terminos">{{ __('login.footer_terms') }}</a>
                    <a href="mailto:contato@bancodechoices.com">{{ __('login.footer_contact') }}</a>
                </nav>
            </div>
            <div class="col-md-3 d-flex flex-column justify-content-center">
                <p class="auth-login-footer-copy mb-0">{{ __('login.footer_copy') }}</p>
            </div>
        </div>
    </div>
</footer>
