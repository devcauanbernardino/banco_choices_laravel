@extends('layouts.app')

@section('title', __('perfil.page_title'))
@section('mobile_title', __('perfil.mobile_title'))

@section('topbar_title', __('perfil.mobile_title'))

@section('content')
    <div class="container-fluid bc-private-wrap--profile">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Hero banner --}}
        <div class="bc-hero mb-4">
            <div class="row align-items-end g-4 position-relative bc-profile-hero-row">
                <div class="col-md-auto text-center text-md-start">
                    <img class="bc-hero-avatar"
                         src="https://ui-avatars.com/api/?name={{ urlencode($usuario->nome) }}&size=224&background=ffffff&color=6a0392"
                         alt="">
                </div>
                <div class="col-md">
                    <h1 class="h3 fw-bold mb-1 text-white">{{ $usuario->nome }}</h1>
                    <p class="mb-2 opacity-90 small text-white">{{ $usuario->email }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($materias as $mat)
                            <span class="bc-materia-chip">{{ $mat->nome ?? $mat['nome'] ?? '' }}</span>
                        @empty
                            <span class="badge bg-light text-dark">{{ __('perfil.no_materias') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left column --}}
            <div class="col-lg-4">
                {{-- Stats summary --}}
                <div class="bc-card p-4 mb-4">
                    <h2 class="h6 fw-bold mb-3">{{ __('perfil.summary') }}</h2>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="bc-stat-card d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ __('perfil.stat_sims') }}</span>
                                <span class="fw-bold fs-5 text-primary">{{ (int) $totalSimulados }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bc-stat-card d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ __('perfil.stat_questions') }}</span>
                                <span class="fw-bold fs-5 text-primary">{{ number_format((int) $totalQuestoes, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bc-stat-card d-flex justify-content-between align-items-center">
                                <span class="text-muted small">{{ __('perfil.stat_avg') }}</span>
                                <span class="fw-bold fs-5 text-primary">{{ $mediaGeral }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column --}}
            <div class="col-lg-8">
                {{-- Account data form --}}
                <div class="bc-card p-4 mb-4">
                    <h2 class="h6 fw-bold mb-4">{{ __('perfil.account_data') }}</h2>
                    <form action="{{ route('profile.update') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">{{ __('perfil.label_name') }}</label>
                                <input type="text" class="form-control form-control-lg" name="nome" required
                                       value="{{ old('nome', $usuario->nome) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">{{ __('perfil.label_email') }}</label>
                                <input type="email" class="form-control" value="{{ $usuario->email }}" readonly disabled>
                                <div class="form-text">{{ __('perfil.email_help') }}</div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        {{-- Password change --}}
                        <h3 class="h6 fw-bold mb-3">{{ __('perfil.security') }}</h3>
                        <p class="small text-muted">{{ __('perfil.security_hint') }}</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted" for="senhaAtualInput">{{ __('perfil.label_cur_pass') }}</label>
                                <div class="input-group rounded-3 overflow-hidden">
                                    <input type="password" class="form-control" id="senhaAtualInput" name="senha_atual" placeholder="{{ __('perfil.placeholder_current') }}" autocomplete="current-password">
                                    <button type="button" class="btn btn-outline-secondary bc-pw-toggle d-inline-flex align-items-center justify-content-center px-3" id="toggleSenhaAtual" data-bc-pw-target="senhaAtualInput" aria-controls="senhaAtualInput" aria-label="{{ __('login.show_pwd') }}" aria-pressed="false">
                                        <span class="material-icons fs-6 bc-pw-toggle-icon" aria-hidden="true">visibility</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted" for="novaSenhaInput">{{ __('perfil.label_new_pass') }}</label>
                                <div class="input-group rounded-3 overflow-hidden">
                                    <input type="password" class="form-control" id="novaSenhaInput" name="nova_senha" placeholder="{{ __('perfil.placeholder_new') }}" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary bc-pw-toggle d-inline-flex align-items-center justify-content-center px-3" id="toggleNovaSenha" data-bc-pw-target="novaSenhaInput" aria-controls="novaSenhaInput" aria-label="{{ __('login.show_pwd') }}" aria-pressed="false">
                                        <span class="material-icons fs-6 bc-pw-toggle-icon" aria-hidden="true">visibility</span>
                                    </button>
                                </div>
                                <div id="novaSenhaStrengthMeter" class="bc-pw-strength mt-2" hidden>
                                    <div class="bc-pw-strength-track">
                                        <div class="bc-pw-strength-bar" id="novaSenhaStrengthBar"></div>
                                    </div>
                                    <span class="bc-pw-strength-label small" id="novaSenhaStrengthLabel" role="status" aria-live="polite"></span>
                                </div>
                            </div>
                        </div>

                        <div class="bc-form-actions">
                            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-end align-items-center">
                                <a href="{{ route('dashboard') }}"
                                   class="btn btn-outline-secondary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                                    <span class="material-icons bc-perfil-icon-btn" aria-hidden="true">arrow_back</span>
                                    {{ __('perfil.back') }}
                                </a>
                                <button type="submit"
                                        class="btn btn-primary btn-lg px-4 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <span class="material-icons bc-perfil-icon-btn" aria-hidden="true">save</span>
                                    {{ __('perfil.save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Logout section --}}
                <div class="bc-card p-4 border-danger border-opacity-25">
                    <h2 class="h6 fw-bold mb-2 text-danger">{{ __('perfil.logout_section') }}</h2>
                    <p class="small text-muted mb-3">{{ __('perfil.logout_hint') }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center gap-2">
                            <span class="material-icons fs-6" aria-hidden="true">logout</span> {{ __('perfil.logout_btn') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var showPwd = @json(__('login.show_pwd'));
    var hidePwd = @json(__('login.hide_pwd'));
    document.querySelectorAll('[data-bc-pw-target]').forEach(function (btn) {
        var inputId = btn.getAttribute('data-bc-pw-target');
        var inp = document.getElementById(inputId);
        if (!inp) return;
        var icon = btn.querySelector('.bc-pw-toggle-icon');
        btn.addEventListener('click', function () {
            var reveal = inp.type === 'password';
            inp.type = reveal ? 'text' : 'password';
            if (icon) {
                icon.textContent = reveal ? 'visibility_off' : 'visibility';
            }
            btn.setAttribute('aria-label', reveal ? hidePwd : showPwd);
            btn.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        });
    });
})();

(function () {
    var input = document.getElementById('novaSenhaInput');
    var meter = document.getElementById('novaSenhaStrengthMeter');
    var bar = document.getElementById('novaSenhaStrengthBar');
    var label = document.getElementById('novaSenhaStrengthLabel');
    if (!input || !meter || !bar || !label) return;

    var t = {
        weak: @json(__('perfil.pw_strength_weak')),
        medium: @json(__('perfil.pw_strength_medium')),
        strong: @json(__('perfil.pw_strength_strong'))
    };

    function classify(pw) {
        if (!pw.length) return null;
        var lower = /[a-z]/.test(pw);
        var upper = /[A-Z]/.test(pw);
        var digit = /[0-9]/.test(pw);
        var special = /[^a-zA-Z0-9]/.test(pw);
        var classes = (lower ? 1 : 0) + (upper ? 1 : 0) + (digit ? 1 : 0) + (special ? 1 : 0);
        if (pw.length < 8 || classes <= 1) return 'weak';
        if (classes === 4 || (pw.length >= 12 && classes >= 3)) return 'strong';
        return 'medium';
    }

    function update() {
        var pw = input.value;
        var level = classify(pw);
        if (!level) {
            meter.hidden = true;
            bar.className = 'bc-pw-strength-bar';
            bar.style.width = '0%';
            label.textContent = '';
            return;
        }
        meter.hidden = false;
        bar.className = 'bc-pw-strength-bar bc-pw-strength-bar--' + level;
        bar.style.width = level === 'weak' ? '33%' : (level === 'medium' ? '66%' : '100%');
        label.textContent = t[level];
    }

    input.addEventListener('input', update);
    input.addEventListener('change', update);
})();
</script>
@endpush
