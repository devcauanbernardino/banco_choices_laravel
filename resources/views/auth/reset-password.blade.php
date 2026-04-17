@extends('layouts.public')

@section('title', __('password.title_reset_page'))

@section('body_attr')
 class="auth-redefinir-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/auth-redefinir-senha.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
@endpush

@section('content')
<div class="auth-redefinir-wrap">
    <div class="auth-redefinir-blobs" aria-hidden="true">
        <div class="auth-redefinir-blobs__one"></div>
        <div class="auth-redefinir-blobs__two"></div>
    </div>

    <header class="auth-redefinir-topbar">
        <a href="{{ route('login') }}" class="auth-redefinir-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;">arrow_back</span>
            <span>{{ __('password.back_login') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="auth-redefinir-main">
        <div class="auth-redefinir-inner">
            <div class="auth-redefinir-card">
            <div class="auth-redefinir-card-head">
                <div class="auth-redefinir-logo">
                    <a href="{{ route('home') }}" class="text-decoration-none d-inline-block" aria-label="{{ __('index.page_title') }}">
                        <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" decoding="async">
                    </a>
                </div>
                <h1 class="auth-redefinir-title">{{ __('password.heading_redefine') }}</h1>
                <p class="auth-redefinir-lead">{{ __('password.lead_reset') }}</p>
            </div>

            <form action="{{ route('password.update') }}" method="post" id="resetForm" class="auth-redefinir-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                @if ($errors->any())
                    <div class="alert alert-danger auth-redefinir-alert alert-dismissible fade show" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('login.close') }}"></button>
                    </div>
                @endif

                <div class="auth-redefinir-fields">
                    <div class="auth-redefinir-field">
                        <label for="emailField">{{ __('login.email') }}</label>
                        <div class="auth-redefinir-input-shell">
                            <span class="material-symbols-outlined" aria-hidden="true">mail</span>
                            @if (! empty($email))
                                <input type="hidden" name="email" value="{{ old('email', $email) }}">
                                <input class="auth-redefinir-input" id="emailField" type="email"
                                       value="{{ old('email', $email) }}" readonly
                                       autocomplete="email" aria-readonly="true">
                            @else
                                <input class="auth-redefinir-input" id="emailField" name="email" type="email"
                                       inputmode="email" autocomplete="email" autocapitalize="none" spellcheck="false"
                                       placeholder="{{ __('login.email_placeholder') }}" required
                                       value="{{ old('email') }}"
                                       aria-required="true">
                            @endif
                        </div>
                    </div>

                    <div class="auth-redefinir-field">
                        <label for="passwordInput">{{ __('password.new_password') }}</label>
                        <div class="auth-redefinir-input-shell auth-redefinir-input-shell--pwd">
                            <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                            <input class="auth-redefinir-input" id="passwordInput" name="password" type="password"
                                   autocomplete="new-password" required
                                   placeholder="{{ __('login.password_placeholder') }}" minlength="8"
                                   aria-required="true">
                            <button type="button" class="auth-redefinir-pwd-toggle" id="togglePassword"
                                    aria-label="{{ __('login.show_pwd') }}" aria-controls="passwordInput" aria-pressed="false">
                                <span class="material-symbols-outlined" id="togglePasswordIcon" aria-hidden="true">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="auth-redefinir-field">
                        <label for="passwordConfirm">{{ __('password.confirm_label') }}</label>
                        <div class="auth-redefinir-input-shell auth-redefinir-input-shell--pwd">
                            <span class="material-symbols-outlined" aria-hidden="true">verified_user</span>
                            <input class="auth-redefinir-input" id="passwordConfirm" name="password_confirmation" type="password"
                                   autocomplete="new-password" required
                                   placeholder="{{ __('password.confirm_placeholder') }}" minlength="8"
                                   aria-required="true">
                            <button type="button" class="auth-redefinir-pwd-toggle" id="togglePasswordConfirm"
                                    aria-label="{{ __('login.show_pwd') }}" aria-controls="passwordConfirm" aria-pressed="false">
                                <span class="material-symbols-outlined" id="togglePasswordConfirmIcon" aria-hidden="true">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="auth-redefinir-security">
                    <h3>{{ __('password.security_title') }}</h3>
                    <ul class="auth-redefinir-reqs">
                        <li>
                            <span class="auth-redefinir-req-icon" aria-hidden="true">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                            <span>{{ __('password.req_min') }}</span>
                        </li>
                        <li>
                            <span class="auth-redefinir-req-icon" aria-hidden="true">
                                <span class="material-symbols-outlined">check_circle</span>
                            </span>
                            <span>{{ __('password.req_case') }}</span>
                        </li>
                        <li>
                            <span class="auth-redefinir-req-icon" aria-hidden="true">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                            <span>{{ __('password.req_special') }}</span>
                        </li>
                        <li>
                            <span class="auth-redefinir-req-icon" aria-hidden="true">
                                <span class="material-symbols-outlined">check</span>
                            </span>
                            <span>{{ __('password.req_number') }}</span>
                        </li>
                    </ul>
                </div>

                <button class="auth-redefinir-submit" type="submit" id="submitBtn">
                    <span>{{ __('password.submit_reset') }}</span>
                    <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                </button>
            </form>

            <div class="auth-login-divider">
                <p>{{ __('login.signup_prompt') }}</p>
                <a href="{{ route('signup.materias') }}">{{ __('login.signup_link') }}</a>
            </div>
            </div>

        </div>
    </main>

    @include('components.auth-login-footer')
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/reload.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('resetForm');
        const btn = document.getElementById('submitBtn');

        function bindPwdToggle(toggle, pwd, icon) {
            if (!toggle || !pwd || !icon) return;
            toggle.addEventListener('click', function () {
                const show = pwd.type === 'password';
                pwd.type = show ? 'text' : 'password';
                icon.textContent = show ? 'visibility_off' : 'visibility';
                toggle.setAttribute('aria-label', show ? @json(__('login.hide_pwd')) : @json(__('login.show_pwd')));
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        }

        bindPwdToggle(
            document.getElementById('togglePassword'),
            document.getElementById('passwordInput'),
            document.getElementById('togglePasswordIcon')
        );
        bindPwdToggle(
            document.getElementById('togglePasswordConfirm'),
            document.getElementById('passwordConfirm'),
            document.getElementById('togglePasswordConfirmIcon')
        );

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.classList.add('btn-loading');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + @json(__('password.submitting_reset'));
            });
        }
    });
</script>
@endpush
