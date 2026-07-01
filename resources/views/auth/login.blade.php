@extends('layouts.public')

@section('title', __('login.title_page'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: none; } }
@keyframes gradShift { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
@keyframes glowPulse { 0%,100% { opacity:.55; } 50% { opacity:.85; } }
@keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

.login-page {
    display: grid;
    grid-template-columns: 1fr 1fr;
    height: 100svh;
    font-family: 'Inter', system-ui, sans-serif;
}
.login-hero {
    position: relative;
    background: #2a1238;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    padding: clamp(32px, 5vw, 56px);
}
.login-hero__dots {
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.055) 1px, transparent 1px);
    background-size: 30px 30px;
    pointer-events: none;
}
.login-hero__glow-a {
    position: absolute; top: -10%; left: -10%; width: 80%; height: 70%;
    background: radial-gradient(ellipse at 40% 30%, rgba(155,61,199,.55), transparent 65%);
    pointer-events: none; animation: glowPulse 6s ease-in-out infinite;
}
.login-hero__glow-b {
    position: absolute; bottom: 0; right: 0; width: 60%; height: 50%;
    background: radial-gradient(ellipse at 80% 90%, rgba(139,31,184,.28), transparent 60%);
    pointer-events: none;
}
.login-hero__back {
    position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px;
    color: rgba(255,255,255,.6); font-size: .85rem; font-weight: 500; text-decoration: none;
    transition: color .18s ease; animation: fadeUp .6s ease both;
}
.login-hero__back:hover { color: #fff; }
.login-hero__copy { position: relative; z-index: 1; margin: auto 0; animation: fadeUp .8s .1s ease both; }
.login-hero__eyebrow { font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.38); margin: 0 0 16px; }
.login-hero__title { font-family: 'Poppins',sans-serif; font-weight: 800; font-size: clamp(2rem,3.2vw,2.9rem); line-height: 1.06; letter-spacing: -.035em; color: #fff; margin: 0 0 20px; }
.login-hero__title em {
    font-style: normal; background: linear-gradient(135deg, #e2b8ff, #c084fc, #9333ea);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
    background-size: 200% 200%; animation: gradShift 7s ease-in-out infinite;
}
.login-hero__lead { color: rgba(255,255,255,.45); font-size: .92rem; line-height: 1.65; max-width: 340px; margin: 0; }
.login-hero__stats { display: flex; gap: 28px; margin-top: 36px; flex-wrap: wrap; }
.login-hero__stat-num {
    font-family: 'Poppins',sans-serif; font-weight: 800; font-size: 1.6rem; line-height: 1;
    background: linear-gradient(135deg, #e2b8ff, #c084fc);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
}
.login-hero__stat-label { color: rgba(255,255,255,.38); font-size: .75rem; margin-top: 4px; }
.login-hero__social-proof { color: rgba(255,255,255,.3); font-size: .78rem; margin-top: 20px; line-height: 1.5; }

.login-form-side {
    overflow-y: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f2f5;
    padding: clamp(20px, 4vw, 48px) 16px;
}
@media (max-width: 900px) {
    .login-page { grid-template-columns: 1fr; height: auto; min-height: 100svh; }
    .login-hero { padding: 32px 24px; }
}
.login-card {
    width: 100%;
    max-width: 472px;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, .08);
    padding: clamp(28px, 4vw, 44px);
}
.login-card__head { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px; }
.login-card__icon {
    width: 52px; height: 52px; flex-shrink: 0; border-radius: 14px;
    background: rgba(106, 3, 146, .1);
    color: #6a0392;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
}
.login-card__title { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.5rem; color: #1c1c1f; margin: 0; }
.login-card__subtitle { color: #6b7280; font-size: .9rem; margin: 4px 0 0; }

.lf-field { position: relative; margin-top: 22px; }
.lf-field label {
    position: absolute; top: -9px; left: 14px; z-index: 2;
    background: #fff; padding: 0 6px; line-height: 1.4;
    font-size: .8rem; font-weight: 600; color: #374151;
}
.lf-field label .lf-req { color: #ef4444; }
.lf-field__wrap { position: relative; display: flex; align-items: center; }
.lf-field__wrap > i {
    position: absolute; left: 14px; color: #9ca3af; font-size: 1rem; pointer-events: none;
}
.lf-field input {
    width: 100%; padding: 13px 14px 13px 42px;
    border: 1.5px solid rgba(15, 23, 42, .14); border-radius: 12px;
    font-family: 'Inter', sans-serif; font-size: .92rem; color: #1c1c1f;
    outline: none; background: #fafafa;
    transition: border-color .2s ease, box-shadow .2s ease;
}
.lf-field input:focus { border-color: #6a0392; box-shadow: 0 0 0 3px rgba(106, 3, 146, .1); }
.lf-field--pass input { padding-right: 44px; }
.lf-pass-toggle {
    position: absolute; right: 8px; background: none; border: none; cursor: pointer;
    color: #6b7280; display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; font-size: 1.05rem; line-height: 1; transition: color .18s ease;
}
.lf-pass-toggle:hover { color: #6a0392; }

.login-row { display: flex; align-items: center; justify-content: space-between; margin-top: 18px; }
.login-check { display: flex; align-items: center; gap: 8px; }
.login-check label { font-size: .84rem; color: #6b7280; cursor: pointer; }
.login-forgot { font-size: .82rem; color: #6a0392; text-decoration: none; font-weight: 500; transition: opacity .18s ease; }
.login-forgot:hover { opacity: .7; }

.login-alert {
    margin-top: 18px; padding: 11px 14px; border-radius: 10px;
    background: rgba(220, 38, 38, .08); border: 1px solid rgba(220, 38, 38, .22);
    color: #b91c1c; font-size: .85rem;
}

.login-submit {
    width: 100%; margin-top: 22px; padding: 14px; border-radius: 12px; border: none;
    background: linear-gradient(135deg, #8b1fb8, #6a0392); color: #fff;
    font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .96rem; cursor: pointer;
    box-shadow: 0 8px 24px rgba(106, 3, 146, .3);
    transition: filter .2s ease, box-shadow .2s ease;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.login-submit:hover { filter: brightness(1.07); box-shadow: 0 10px 30px rgba(106, 3, 146, .4); }

.login-divider { display: flex; align-items: center; gap: 12px; margin: 26px 0; }
.login-divider span { flex: 1; height: 1px; background: rgba(15, 23, 42, .09); }
.login-divider small { font-size: .78rem; color: #9ca3af; white-space: nowrap; }

.login-signup {
    display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    padding: 13px; border-radius: 12px; border: 1.5px solid rgba(15, 23, 42, .14);
    background: transparent; color: #1c1c1f; font-family: 'Poppins', sans-serif;
    font-weight: 600; font-size: .9rem; text-decoration: none;
    transition: border-color .2s ease, background .2s ease;
}
.login-signup:hover { border-color: rgba(106, 3, 146, .45); background: rgba(106, 3, 146, .04); }

.login-foot { text-align: center; color: #9ca3af; font-size: .78rem; margin-top: 24px; }

.login-hero__features {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; gap: 10px;
    animation: fadeUp .8s .25s ease both;
}
.login-hero__feat-card {
    display: flex; align-items: center; gap: 14px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 14px;
    padding: 13px 16px;
    backdrop-filter: blur(6px);
    transition: background .2s ease;
}
.login-hero__feat-card:hover { background: rgba(255,255,255,.11); }
.login-hero__feat-ico {
    font-size: 1.35rem; color: #c084fc; flex-shrink: 0;
    font-family: 'Material Symbols Outlined', sans-serif;
}
.login-hero__feat-title { font-weight: 600; font-size: .86rem; color: #fff; line-height: 1.3; }
.login-hero__feat-desc { font-size: .76rem; color: rgba(255,255,255,.38); margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="login-page">
    <div class="login-hero">
        <span class="login-hero__dots" aria-hidden="true"></span>
        <span class="login-hero__glow-a" aria-hidden="true"></span>
        <span class="login-hero__glow-b" aria-hidden="true"></span>

        <a href="{{ route('home') }}" class="login-hero__back">
            <i class="bi bi-arrow-left"></i>
            {{ __('login.back_home') }}
        </a>

        <div class="login-hero__copy">
            <p class="login-hero__eyebrow">{{ __('login.portal_tagline') }}</p>
            <h2 class="login-hero__title">{{ __('login.sidebar_heading') }}</h2>
            <p class="login-hero__lead">{{ __('login.sidebar_lead') }}</p>

            <div class="login-hero__stats">
                <div>
                    <div class="login-hero__stat-num">47k+</div>
                    <div class="login-hero__stat-label">{{ __('login.sidebar_stat_questions') }}</div>
                </div>
                <div>
                    <div class="login-hero__stat-num">12k+</div>
                    <div class="login-hero__stat-label">{{ __('login.sidebar_stat_approved') }}</div>
                </div>
                <div>
                    <div class="login-hero__stat-num">95%</div>
                    <div class="login-hero__stat-label">{{ __('login.sidebar_stat_rate') }}</div>
                </div>
            </div>
            <p class="login-hero__social-proof">{{ __('login.sidebar_social_proof') }}</p>
        </div>

        <div class="login-hero__features">
            <div class="login-hero__feat-card">
                <span class="material-symbols-outlined login-hero__feat-ico" aria-hidden="true">menu_book</span>
                <div>
                    <div class="login-hero__feat-title">{{ __('login.feat1_title') }}</div>
                    <div class="login-hero__feat-desc">{{ __('login.feat1_desc') }}</div>
                </div>
            </div>
            <div class="login-hero__feat-card">
                <span class="material-symbols-outlined login-hero__feat-ico" aria-hidden="true">timer</span>
                <div>
                    <div class="login-hero__feat-title">{{ __('login.feat2_title') }}</div>
                    <div class="login-hero__feat-desc">{{ __('login.feat2_desc') }}</div>
                </div>
            </div>
            <div class="login-hero__feat-card">
                <span class="material-symbols-outlined login-hero__feat-ico" aria-hidden="true">insights</span>
                <div>
                    <div class="login-hero__feat-title">{{ __('login.feat3_title') }}</div>
                    <div class="login-hero__feat-desc">{{ __('login.feat3_desc') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="login-form-side">
    <div class="login-card">
        <div class="login-card__head">
            <span class="login-card__icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
            <div>
                <h1 class="login-card__title">{{ __('login.heading') }}</h1>
                <p class="login-card__subtitle">{{ __('login.portal_tagline') }}</p>
            </div>
        </div>

        @if (session('error'))
            <div class="login-alert">{{ __('login.err.'.session('error')) }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="lf-field">
                <label for="login-email">{{ __('login.email') }} <span class="lf-req">*</span></label>
                <div class="lf-field__wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" id="login-email" name="email" value="{{ old('email') }}" placeholder="{{ __('login.email_placeholder') }}" required autofocus>
                </div>
                @error('email') <div class="login-alert">{{ $message }}</div> @enderror
            </div>

            <div class="lf-field lf-field--pass">
                <label for="login-senha">{{ __('login.password') }} <span class="lf-req">*</span></label>
                <div class="lf-field__wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="login-senha" name="senha" placeholder="{{ __('login.password_placeholder') }}" required>
                    <button type="button" class="lf-pass-toggle" aria-label="{{ __('login.show_pwd') }}" onclick="const f=document.getElementById('login-senha'); const i=this.querySelector('i'); const show=f.type==='password'; f.type = show ? 'text' : 'password'; i.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('senha') <div class="login-alert">{{ $message }}</div> @enderror
            </div>

            <div class="login-row">
                <div class="login-check">
                    <input type="checkbox" class="form-check-input" id="login-remember" name="remember">
                    <label for="login-remember">{{ __('login.remember') }}</label>
                </div>
                <a href="{{ route('password.request') }}" class="login-forgot">{{ __('login.forgot') }}</a>
            </div>

            <button type="submit" class="login-submit">
                {{ __('login.submit') }}
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <div class="login-divider">
            <span></span>
            <small>{{ __('login.signup_prompt') }}</small>
            <span></span>
        </div>

        <a href="{{ route('signup.materias') }}" class="login-signup">
            <i class="bi bi-person-plus"></i>
            {{ __('login.signup_link') }}
        </a>

        <p class="login-foot">© {{ date('Y') }} {{ __('landing.footer.copyright') }}</p>
    </div>
    </div>
</div>
@endsection
