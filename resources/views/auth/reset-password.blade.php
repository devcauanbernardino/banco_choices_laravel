@extends('layouts.public')

@section('title', __('password.title_reset_page'))

@section('body_attr')
 class="login-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
@endpush

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0 login-wrapper">
        <div class="col-lg-7 d-none d-lg-flex login-sidebar align-items-center justify-content-center p-5 text-white"
             style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC445AKHSVeDzgOnDg89cqG-J45BnnX0jlKJqEDVoAqDa9PF3GuM8AV8eTUyanRvwnfvHSOOc9cPkyCbrND0UX4AnWDqxH2GdbLBAi9kTxBbiKYwhJwpp4McWRaQzKp14-JLsiLfjttFhj-vIaYBR95BlK0Z6arvuWAXGmsEtoBH76JvcIP81a7sjWaeBwLZayIcGfCms3TkEBhVMG3vnN2NFTTcLzxwCoLuoIZokjnUni0LZX0MQe68-QmFcZSHglB4zvHEoKo4mBK');">
            <div class="login-sidebar-overlay"></div>
            <div class="sidebar-content mw-100" style="max-width: 600px;">
                <div class="mb-4">
                    <div class="login-sidebar-logo-wrap">
                        <img class="login-sidebar-logo" src="{{ \App\Support\Branding::logoUrl() }}"
                             alt="" width="200" height="56">
                    </div>
                </div>
                <h1 class="display-4 fw-bold mb-4">{{ __('login.sidebar_heading') }}</h1>
                <p class="lead mb-5 opacity-75">{{ __('login.sidebar_lead') }}</p>
            </div>
        </div>

        <div class="col-12 col-lg-5 login-form-column">
            <div class="login-form-inner">
                <div class="login-form-container">
                    <div class="d-lg-none login-mobile-brand text-center">
                        <a href="{{ route('home') }}" class="login-mobile-logo-link d-inline-block text-decoration-none" aria-label="{{ __('index.page_title') }}">
                            <img class="login-mobile-logo" src="{{ \App\Support\Branding::logoUrl() }}"
                                 alt="" width="280" height="78" decoding="async">
                        </a>
                    </div>

                    <header class="login-form-header">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-2">
                            <a href="{{ route('login') }}" class="login-back-link mb-0 align-self-center">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                <span>{{ __('password.back_login') }}</span>
                            </a>
                            <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
                                <div class="navbar-actions__inner">
                                    @include('components.language-selector')
                                </div>
                            </div>
                        </div>
                        <h2 class="login-title">{{ __('password.heading_reset') }}</h2>
                    </header>

                    @if ($errors->any())
                        <div class="alert alert-danger login-alert alert-dismissible fade show" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="post" id="resetForm" class="login-form">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="login-field">
                            <label class="login-field-label" for="emailField">
                                {{ __('login.email') }}
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                                @if(!empty($email))
                                    <input type="hidden" name="email" value="{{ old('email', $email) }}">
                                    <input class="form-control" id="emailField" type="email"
                                           value="{{ old('email', $email) }}" readonly
                                           aria-readonly="true">
                                @else
                                    <input class="form-control" id="emailField" name="email" type="email"
                                           inputmode="email" autocomplete="email" autocapitalize="none" spellcheck="false"
                                           placeholder="{{ __('login.email_placeholder') }}" required
                                           value="{{ old('email') }}"
                                           aria-required="true">
                                @endif
                            </div>
                        </div>

                        <div class="login-field">
                            <label class="login-field-label" for="passwordInput">
                                {{ __('password.new_password') }}
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                                <input class="form-control" id="passwordInput" name="password" type="password"
                                       autocomplete="new-password" required
                                       placeholder="{{ __('login.password_placeholder') }}" minlength="8"
                                       aria-required="true">
                                <button type="button" class="btn login-password-toggle" id="togglePassword"
                                        aria-label="{{ __('login.show_pwd') }}" aria-controls="passwordInput" aria-pressed="false">
                                    <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-field">
                            <label class="login-field-label" for="passwordConfirm">
                                {{ __('password.confirm_label') }}
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock-fill"></i></span>
                                <input class="form-control" id="passwordConfirm" name="password_confirmation" type="password"
                                       autocomplete="new-password" required
                                       placeholder="{{ __('password.confirm_placeholder') }}" minlength="8"
                                       aria-required="true">
                                <button type="button" class="btn login-password-toggle" id="togglePasswordConfirm"
                                        aria-label="{{ __('login.show_pwd') }}" aria-controls="passwordConfirm" aria-pressed="false">
                                    <i class="bi bi-eye" id="togglePasswordConfirmIcon" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid login-submit-wrap">
                            <button class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100" type="submit" id="submitBtn">
                                {{ __('password.submit_reset') }} <i class="bi bi-check2-circle ms-2" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>

                    <footer class="login-footer">
                        <nav class="login-footer-nav" aria-label="Legal">
                            <a href="{{ route('home') }}#privacidad">{{ __('login.footer_privacy') }}</a>
                            <span class="login-footer-dot" aria-hidden="true"></span>
                            <a href="{{ route('home') }}#terminos">{{ __('login.footer_terms') }}</a>
                            <span class="login-footer-dot" aria-hidden="true"></span>
                            <a href="mailto:contato@bancodechoices.com">{{ __('login.footer_contact') }}</a>
                        </nav>
                        <p class="login-footer-copy">{{ __('login.footer_copy') }}</p>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/reload.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('resetForm');
        const btn = document.getElementById('submitBtn');

        function bindToggle(toggle, input, icon) {
            if (!toggle || !input || !icon) return;
            toggle.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                toggle.setAttribute('aria-label', show ? @json(__('login.hide_pwd')) : @json(__('login.show_pwd')));
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        }

        bindToggle(
            document.getElementById('togglePassword'),
            document.getElementById('passwordInput'),
            document.getElementById('togglePasswordIcon')
        );
        bindToggle(
            document.getElementById('togglePasswordConfirm'),
            document.getElementById('passwordConfirm'),
            document.getElementById('togglePasswordConfirmIcon')
        );

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.classList.add('btn-loading');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + @json(__('password.submitting_reset'));
            });
        }
    });
</script>
@endpush
