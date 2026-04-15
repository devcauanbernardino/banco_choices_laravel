@extends('layouts.public')

@section('title', __('signup.page_title.plano'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/top-bar-brand.css') }}">
<style>
    :root {
        --accent-purple: #6a0392;
        --accent-purple-light: rgba(106,3,146,0.55);
        --accent-purple-lighter: rgba(106,3,146,0.12);
        --navy-primary: #002147;
    }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 50%, #460161 100%);
        background-size: 160% 160%;
        animation: floatBg 14s ease-in-out infinite;
        min-height: 100vh;
    }
    @keyframes floatBg {
        0% { background-position: 0% 0%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 0%; }
    }
    .container-custom { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
    .header-section { text-align: center; margin-bottom: 3rem; color: white; }
    .header-section h1 { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 2.5rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
    .step-indicator { display: flex; justify-content: space-between; margin-bottom: 3rem; position: relative; }
    .step-indicator::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: rgba(255,255,255,0.2); }
    .step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; flex: 1; }
    .step-number { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 2px solid white; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; margin-bottom: 0.5rem; }
    .step.active .step-number { background: var(--accent-purple); box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter); }
    .step-label { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.7); text-transform: uppercase; }
    .step.active .step-label { color: white; }
    .plans-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .plan-form { display: flex; min-height: 100%; }
    .plan-card { background: rgba(255,255,255,0.98); border-radius: 20px; border: 2px solid #e5e7eb; padding: 1.35rem 1.5rem 1.5rem; position: relative; transition: border-color 0.25s, box-shadow 0.25s, transform 0.25s; width: 100%; display: flex; flex-direction: column; }
    .plan-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; border-radius: 20px 20px 0 0; background: linear-gradient(90deg, var(--accent-purple), var(--accent-purple-light)); }
    .plan-card:hover { border-color: var(--accent-purple); transform: translateY(-4px); box-shadow: 0 18px 40px rgba(106,3,146,0.18); }
    .plan-card.popular { border-color: var(--accent-purple); box-shadow: 0 16px 36px rgba(106,3,146,0.22); }
    .plan-card.popular::before { height: 6px; }
    .plan-badge-slot { min-height: 2rem; display: flex; justify-content: center; align-items: center; margin: 0 0 0.75rem; }
    .plan-badge { display: inline-block; background: linear-gradient(135deg, var(--accent-purple), #8b2e9e); color: white; padding: 0.35rem 0.9rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; box-shadow: 0 4px 14px rgba(106,3,146,0.3); }
    .plan-name { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.25rem; color: var(--navy-primary); margin: 0 0 0.45rem; }
    .plan-description { font-size: 0.88rem; color: #6b7280; margin: 0; }
    .plan-price-block { margin-bottom: 1.15rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(0,33,71,0.07); }
    .plan-price { margin: 0 0 0.35rem; display: flex; align-items: baseline; gap: 0.4rem; }
    .plan-price-amount { font-family: 'Poppins', sans-serif; font-size: clamp(1.55rem, 3.5vw, 2rem); font-weight: 800; background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .plan-price-currency { font-size: 0.9rem; font-weight: 700; color: var(--accent-purple); }
    .plan-price-period { font-size: 0.86rem; color: #6b7280; margin: 0; }
    .plan-features { list-style: none; margin: 0 0 1.25rem; padding: 0; flex: 1; }
    .plan-features li { display: flex; align-items: flex-start; gap: 0.55rem; padding: 0.35rem 0; font-size: 0.875rem; color: #374151; }
    .plan-features li i { color: var(--accent-purple); font-size: 1rem; margin-top: 0.12rem; }
    .plan-card-actions { margin-top: auto; padding-top: 0.5rem; }
    .selected-materias { background: rgba(255,255,255,0.98); border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .selected-materias h3 { font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--navy-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .materias-list { display: flex; flex-wrap: wrap; gap: 0.75rem; }
    .materia-tag { background: var(--accent-purple-lighter); border: 1px solid var(--accent-purple); color: var(--navy-primary); padding: 0.5rem 0.85rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.45rem; }
    .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; font-weight: 600; }
    .back-link:hover { gap: 0.75rem; color: rgba(255,255,255,0.8); }
    @media (max-width: 768px) {
        .plans-container { grid-template-columns: 1fr; }
        .header-section h1 { font-size: 1.75rem; }
    }
</style>
@endpush

@section('content')
<main class="container-custom">
    {{-- Top bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
        <a href="{{ route('home') }}" class="brand-mark" aria-label="Banco de Choices">
            <img src="{{ asset('img/logo-bd-transparente.png') }}" alt="Banco de Choices" width="180" height="40">
        </a>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @include('components.language-selector')
            <a href="{{ route('signup.materias') }}" class="back-link">
                <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                {{ __('signup.back_materias') }}
            </a>
        </div>
    </div>

    {{-- Header --}}
    <div class="header-section">
        <h1>{{ __('signup.plano.h1') }}</h1>
        <p>{{ __('signup.plano.lead') }}</p>
    </div>

    {{-- Step indicator --}}
    <div class="step-indicator" aria-label="{{ __('signup.steps.aria') }}">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-label">{{ __('signup.step.materias') }}</div>
        </div>
        <div class="step active" aria-current="step">
            <div class="step-number">2</div>
            <div class="step-label">{{ __('signup.step.plan') }}</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-label">{{ __('signup.step.pago') }}</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-label">{{ __('signup.step.confirmacion') }}</div>
        </div>
    </div>

    {{-- Selected subjects --}}
    <div class="selected-materias">
        <h3>
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            {{ __('signup.plano.selected') }}
        </h3>
        <div class="materias-list">
            @foreach ($materiasInfo as $materia)
                <span class="materia-tag">
                    <i class="bi bi-book" aria-hidden="true"></i>
                    <span>{{ $materia['nome'] }}</span>
                </span>
            @endforeach
        </div>
    </div>

    {{-- Plans --}}
    <div class="plans-container">
        @foreach ($plans as $plan)
            <form method="POST" class="plan-form" action="{{ url()->current() }}">
                @csrf
                <div class="plan-card{{ $plan['popular'] ? ' popular' : '' }}">
                    <div class="plan-badge-slot">
                        @if (!empty($plan['badge']))
                            <span class="plan-badge">{{ $plan['badge'] }}</span>
                        @endif
                    </div>

                    <div class="plan-card-head mb-3">
                        <h2 class="plan-name">{{ $plan['name'] }}</h2>
                        <p class="plan-description">{{ $plan['description'] }}</p>
                    </div>

                    <div class="plan-price-block">
                        <div class="plan-price">
                            <span class="plan-price-amount">$ {{ number_format($plan['price'] * count($materiasInfo), 2, ',', '.') }}</span>
                            <span class="plan-price-currency">ARS</span>
                        </div>
                        <p class="plan-price-period">Total &middot; {{ $plan['duration'] }}</p>
                    </div>

                    <ul class="plan-features">
                        @foreach ($plan['features'] as $feature)
                            <li>
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="plan-card-actions">
                        <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2">
                            {{ __('signup.plano.choose') }}
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</main>
@endsection
