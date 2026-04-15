@extends('layouts.public')

@section('title', __('login.title_page'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0 login-wrapper">
        {{-- Left sidebar --}}
        <div class="col-lg-7 d-none d-lg-flex login-sidebar align-items-center justify-content-center p-5 text-white"
             style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC445AKHSVeDzgOnDg89cqG-J45BnnX0jlKJqEDVoAqDa9PF3GuM8AV8eTUyanRvwnfvHSOOc9cPkyCbrND0UX4AnWDqxH2GdbLBAi9kTxBbiKYwhJwpp4McWRaQzKp14-JLsiLfjttFhj-vIaYBR95BlK0Z6arvuWAXGmsEtoBH76JvcIP81a7sjWaeBwLZayIcGfCms3TkEBhVMG3vnN2NFTTcLzxwCoLuoIZokjnUni0LZX0MQe68-QmFcZSHglB4zvHEoKo4mBK');">
            <div class="login-sidebar-overlay"></div>
            <div class="sidebar-content mw-100" style="max-width: 600px;">
                <div class="mb-4">
                    <div class="login-sidebar-logo-wrap">
                        <img class="login-sidebar-logo" src="{{ asset('img/logo-bd-transparente.png') }}"
                             alt="Banco de Choices" width="200" height="56">
                    </div>
                </div>
                <h1 class="display-4 fw-bold mb-4">{{ __('login.sidebar_heading') }}</h1>
                <p class="lead mb-5 opacity-75">{{ __('login.sidebar_lead') }}</p>
                <div class="d-flex align-items-center">
                    <span class="small opacity-75">{{ __('login.sidebar_social_proof') }}</span>
                </div>
            </div>
        </div>

        {{-- Right form column --}}
        <div class="col-12 col-lg-5 login-form-column">
            <div class="login-form-inner">
                <div class="login-form-container">
                    {{-- Mobile logo --}}
                    <div class="d-lg-none login-mobile-brand text-center">
                        <a href="{{ route('home') }}" class="login-mobile-logo-link d-inline-block text-decoration-none" aria-label="Banco de Choices">
                            <img class="login-mobile-logo" src="{{ asset('img/logo-bd-transparente.png') }}"
                                 alt="Banco de Choices" width="280" height="78" decoding="async">
                        </a>
                    </div>

                    <header class="login-form-header">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-2">
                            <a href="{{ route('home') }}" class="login-back-link mb-0 align-self-center">
                                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                <span>{{ __('login.back_home') }}</span>
                            </a>
                            <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
                                <div class="navbar-actions__inner">
                                    @include('components.language-selector')
                                </div>
                            </div>
                        </div>
                        <h2 class="login-title">{{ __('login.heading') }}</h2>
                    </header>

                    {{-- Success message --}}
                    @if (session('registered'))
                        <div class="alert alert-success login-alert alert-dismissible fade show" role="alert">
                            {{ __('login.success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                        </div>
                    @endif

                    {{-- Error message --}}
                    @if (session('error'))
                        <div class="alert alert-warning login-alert alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger login-alert alert-dismissible fade show" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                        </div>
                    @endif

                    {{-- Login form --}}
                    <form action="{{ route('login') }}" method="post" id="loginForm" class="login-form">
                        @csrf
                        <div class="login-field">
                            <label class="login-field-label" for="emailInput">
                                {{ __('login.email') }}
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                                <input class="form-control" id="emailInput" name="email" type="email"
                                       inputmode="email" autocomplete="email" autocapitalize="none" spellcheck="false"
                                       placeholder="{{ __('login.email_placeholder') }}" required
                                       value="{{ old('email') }}"
                                       aria-required="true">
                            </div>
                        </div>

                        <div class="login-field">
                            <label class="login-field-label" for="passwordInput">
                                {{ __('login.password') }}
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                                <input class="form-control" id="passwordInput" name="senha" type="password"
                                       autocomplete="current-password" required
                                       placeholder="--------" minlength="1"
                                       aria-required="true">
                                <button type="button" class="btn login-password-toggle" id="togglePassword"
                                        aria-label="{{ __('login.show_pwd') }}" aria-controls="passwordInput" aria-pressed="false">
                                    <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center login-row-extras">
                            <div class="form-check">
                                <input class="form-check-input" id="rememberMe" type="checkbox" name="remember">
                                <label class="form-check-label small" for="rememberMe">
                                    {{ __('login.remember') }}
                                </label>
                            </div>
                            <a class="text-navy text-decoration-none small fw-bold" href="mailto:contato@bancodechoices.com">{{ __('login.forgot') }}</a>
                        </div>

                        <div class="d-grid login-submit-wrap">
                            <button class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100" type="submit" id="submitBtn">
                                {{ __('login.submit') }} <i class="bi bi-box-arrow-in-right ms-2" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>

                    <div class="login-signup-cta">
                        <p class="login-signup-text mb-0">
                            {{ __('login.signup') }}
                            <a class="login-signup-link" href="{{ route('signup.materias') }}">{{ __('login.signup_link') }}</a>
                        </p>
                    </div>

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
                icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                toggle.setAttribute('aria-label', show ? @json(__('login.hide_pwd')) : @json(__('login.show_pwd')));
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        }

        form.addEventListener('submit', function () {
            btn.classList.add('btn-loading');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + @json(__('login.submitting'));
        });
    });
</script>
@endpush
