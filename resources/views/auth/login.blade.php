@extends('layouts.public')

@section('title', __('login.title_page'))

@section('body_attr')
 class="auth-login-exemplo"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/login-exemplo.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
@endpush

@section('content')
<div class="auth-login-wrap">
    <div class="auth-login-bg" aria-hidden="true">
        <div class="auth-login-bg-blob auth-login-bg-blob--1"></div>
        <div class="auth-login-bg-blob auth-login-bg-blob--2"></div>
    </div>

    <header class="auth-login-topbar">
        <a href="{{ route('home') }}" class="auth-login-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;">arrow_back</span>
            <span>{{ __('login.back_home') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="auth-login-main">
        <div class="auth-login-inner">
            <div class="auth-login-brand">
                <div class="auth-login-brand-logo">
                    <a href="{{ route('home') }}" class="text-decoration-none d-inline-block" aria-label="{{ __('index.page_title') }}">
                        <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="280" height="90" decoding="async">
                    </a>
                </div>
                <p class="auth-login-tagline">{{ __('login.portal_tagline') }}</p>
            </div>

            <div class="auth-login-card">
                @if (session('registered'))
                    <div class="alert alert-success auth-login-alert alert-dismissible fade show" role="alert">
                        {{ __('login.success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success auth-login-alert alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-warning auth-login-alert alert-dismissible fade show" role="alert">
                        {{ __('login.err.' . session('error')) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger auth-login-alert alert-dismissible fade show" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post" id="loginForm" class="auth-login-form">
                    @csrf
                    <div class="auth-login-field">
                        <label for="emailInput">{{ __('login.email') }}</label>
                        <div class="auth-login-input-wrap">
                            <span class="material-symbols-outlined" aria-hidden="true">mail</span>
                            <input class="auth-login-input" id="emailInput" name="email" type="email"
                                   inputmode="email" autocomplete="email" autocapitalize="none" spellcheck="false"
                                   placeholder="{{ __('login.email_placeholder') }}" required
                                   value="{{ old('email') }}"
                                   aria-required="true">
                        </div>
                    </div>

                    <div class="auth-login-field">
                        <div class="auth-login-field-head">
                            <label for="passwordInput">{{ __('login.password') }}</label>
                            <a class="auth-login-forgot" href="{{ route('password.request') }}">{{ __('login.forgot') }}</a>
                        </div>
                        <div class="auth-login-input-wrap auth-login-input-wrap--pwd">
                            <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                            <input class="auth-login-input" id="passwordInput" name="senha" type="password"
                                   autocomplete="current-password" required
                                   placeholder="{{ __('login.password_placeholder') }}" minlength="1"
                                   aria-required="true">
                            <button type="button" class="auth-login-pwd-toggle" id="togglePassword"
                                    aria-label="{{ __('login.show_pwd') }}" aria-controls="passwordInput" aria-pressed="false">
                                <span class="material-symbols-outlined" id="togglePasswordIcon" aria-hidden="true">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="auth-login-check">
                        <input class="form-check-input" id="rememberMe" type="checkbox" name="remember" value="1">
                        <label class="form-check-label" for="rememberMe">{{ __('login.remember') }}</label>
                    </div>

                    <button class="auth-login-submit" type="submit" id="submitBtn">
                        <span data-login-submit-label>{{ __('login.submit_long') }}</span>
                        <span class="material-symbols-outlined" aria-hidden="true">login</span>
                    </button>
                </form>

                <div class="auth-login-divider">
                    <p>{{ __('login.signup_prompt') }}</p>
                    <a href="{{ route('signup.materias') }}">{{ __('login.signup_link') }}</a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/reload.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('loginForm');
        const btn = document.getElementById('submitBtn');
        const pwd = document.getElementById('passwordInput');
        const toggle = document.getElementById('togglePassword');
        const icon = document.getElementById('togglePasswordIcon');
        if (toggle && pwd && icon) {
            toggle.addEventListener('click', function () {
                const show = pwd.type === 'password';
                pwd.type = show ? 'text' : 'password';
                icon.textContent = show ? 'visibility_off' : 'visibility';
                toggle.setAttribute('aria-label', show ? @json(__('login.hide_pwd')) : @json(__('login.show_pwd')));
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        }

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.classList.add('btn-loading');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + @json(__('login.submitting'));
            });
        }
    });
</script>
@endpush
