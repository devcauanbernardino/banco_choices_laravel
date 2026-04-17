@extends('layouts.public')

@section('title', __('password.title_forgot_page'))

@section('body_attr')
 class="auth-recuperar-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/auth-recuperar-senha.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
@endpush

@section('content')
<div class="auth-recuperar-wrap">
    <div class="auth-recuperar-blobs" aria-hidden="true">
        <div class="auth-recuperar-blobs__one"></div>
        <div class="auth-recuperar-blobs__two"></div>
    </div>

    <header class="auth-recuperar-topbar">
        <a href="{{ route('login') }}" class="auth-recuperar-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;">arrow_back</span>
            <span>{{ __('password.back_login') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="auth-recuperar-main">
        <div class="auth-recuperar-inner">
            <div class="auth-recuperar-brand">
                <div class="auth-recuperar-brand-logo">
                    <a href="{{ route('home') }}" class="text-decoration-none d-inline-block" aria-label="{{ __('index.page_title') }}">
                        <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="280" height="90" decoding="async">
                    </a>
                </div>
            </div>

            <div class="auth-recuperar-card">
            <header class="auth-recuperar-card-head">
                <h2>{{ __('password.heading_forgot') }}</h2>
                <p>{{ __('password.lead_forgot') }}</p>
            </header>

            @if (session('status'))
                <div class="alert alert-success auth-recuperar-alert alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger auth-recuperar-alert alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="post" id="forgotForm" class="auth-recuperar-form">
                @csrf
                <div class="auth-recuperar-field">
                    <label for="emailInput">{{ __('login.email') }}</label>
                    <div class="auth-recuperar-input-wrap">
                        <input class="auth-recuperar-input" id="emailInput" name="email" type="email"
                               inputmode="email" autocomplete="email" autocapitalize="none" spellcheck="false"
                               placeholder="{{ __('login.email_placeholder') }}" required
                               value="{{ old('email') }}"
                               aria-required="true">
                        <span class="auth-recuperar-input-icon" aria-hidden="true">
                            <span class="material-symbols-outlined">alternate_email</span>
                        </span>
                    </div>
                    <p class="auth-recuperar-hint">{{ __('password.email_hint') }}</p>
                </div>

                <button class="auth-recuperar-submit" type="submit" id="submitBtn">
                    <span>{{ __('password.send_link') }}</span>
                    <span class="material-symbols-outlined" aria-hidden="true">send</span>
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
        const form = document.getElementById('forgotForm');
        const btn = document.getElementById('submitBtn');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.classList.add('btn-loading');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + @json(__('password.submitting_forgot'));
            });
        }
    });
</script>
@endpush
