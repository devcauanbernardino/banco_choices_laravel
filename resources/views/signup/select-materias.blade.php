@extends('layouts.public')

@section('title', __('signup.page_title.materias'))

@section('body_attr')
 class="signup-materias-page"
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/signup-select-materias.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/auth-footer-shared.css') }}">
<style>
.signup-materias-cols { display: grid; grid-template-columns: 1fr 300px; gap: 22px; align-items: start; }
@media (max-width: 900px) { .signup-materias-cols { grid-template-columns: 1fr; } }
.signup-materias-panel { background: #fff; border: 1px solid rgba(15,23,42,.08); border-radius: 18px; padding: 20px 22px; margin-bottom: 14px; }
.signup-materias-panel__title { font-size: .9rem; font-weight: 700; color: #1c1c1f; margin-bottom: 14px; }
.signup-materias-tip { display: flex; gap: 10px; background: rgba(106,3,146,.06); border: 1px solid rgba(106,3,146,.16); border-radius: 14px; padding: 14px 16px; }
.signup-materias-tip .material-symbols-outlined { color: #6a0392; flex-shrink: 0; }
.signup-materias-tip p { font-size: .82rem; color: #374151; line-height: 1.6; margin: 0; }
.signup-materias-side { position: sticky; top: 80px; }
.signup-materias-side__card { background: #fff; border: 1px solid rgba(15,23,42,.08); border-radius: 20px; padding: 22px; box-shadow: 0 4px 20px rgba(15,23,42,.05); }
.signup-materias-side__title { font-size: .9rem; font-weight: 700; color: #1c1c1f; margin-bottom: 16px; }
.signup-materias-side__text { font-size: .84rem; color: #6b7280; line-height: 1.65; margin-bottom: 18px; }
.signup-materias-side__btn { width: 100%; padding: 13px; border-radius: 13px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-family: 'Plus Jakarta Sans','Inter',sans-serif; font-weight: 700; font-size: .95rem; cursor: pointer; box-shadow: 0 6px 20px rgba(106,3,146,.28); display: inline-flex; align-items: center; justify-content: center; gap: 9px; transition: filter .2s ease, box-shadow .2s ease; }
.signup-materias-side__btn:hover { filter: brightness(1.07); box-shadow: 0 8px 26px rgba(106,3,146,.38); }
.signup-materias-side__btn:disabled { opacity: .55; cursor: not-allowed; filter: none; box-shadow: none; }
</style>
@endpush

@section('content')
<div class="signup-materias-wrap">
    <div class="signup-materias-blobs" aria-hidden="true">
        <div class="signup-materias-blobs__one"></div>
        <div class="signup-materias-blobs__two"></div>
    </div>

    <header class="signup-materias-topbar">
        <a href="{{ route('home') }}" class="signup-materias-back">
            <span class="material-symbols-outlined" style="font-size: 1.125rem;" aria-hidden="true">arrow_back</span>
            <span>{{ __('signup.back_home') }}</span>
        </a>
        <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
            <div class="navbar-actions__inner">
                @include('components.language-selector')
            </div>
        </div>
    </header>

    <main class="signup-materias-main">
        <div class="signup-materias-inner">
            <div class="signup-materias-lead">
                <h1 class="signup-materias-lead__title">{{ __('signup.materias.h1') }}</h1>
                <p class="signup-materias-lead__text">
                    {{ __('signup.materias.lead_before') }}
                    <strong>{{ __('signup.materias.lead_strong') }}</strong>
                    {{ __('signup.materias.lead_after') }}
                </p>
            </div>

            <div class="signup-materias-steps" aria-label="{{ __('signup.steps.aria') }}">
                <div class="signup-materias-steps__item is-active" aria-current="step">
                    <div class="signup-materias-steps__num">1</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.materias') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">2</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.plan') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">3</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.pago') }}</div>
                </div>
                <div class="signup-materias-steps__item">
                    <div class="signup-materias-steps__num">4</div>
                    <div class="signup-materias-steps__label">{{ __('signup.step.confirmacion') }}</div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @push('scripts')
                <script>try { sessionStorage.removeItem('bc_signup_materias_cart'); } catch (e) { /* ignore */ }</script>
                @endpush
            @endif

            <form id="materiasForm" action="{{ route('signup.materias') }}" method="POST">
                @csrf

                <div class="signup-materias-cols">
                    <div>
                        <div class="signup-materias-panel">
                            <h2 class="signup-materias-panel__title">{{ __('signup.catalog.label_materia') }}</h2>
                            @include('partials.catalog-materias-flow', [
                                'excludeIdsCsv' => '',
                                'presetMateriaId' => $presetMateriaId ?? (int) request('materia_id', 0),
                            ])
                        </div>

                        <div class="signup-materias-tip">
                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                            <p>{{ __('signup.materias.tip') }}</p>
                        </div>
                    </div>

                    <aside class="signup-materias-side">
                        <div class="signup-materias-side__card">
                            <h3 class="signup-materias-side__title">{{ __('signup.materias.what_title') }}</h3>
                            <p class="signup-materias-side__text">{{ __('signup.materias.what_p') }}</p>

                            <button type="submit" class="signup-materias-side__btn">
                                {{ __('signup.btn.continue_plan') }}
                                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                            </button>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/signup-select-materias.js') }}"></script>
@endpush
