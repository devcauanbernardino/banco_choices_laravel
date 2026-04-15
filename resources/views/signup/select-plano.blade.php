@extends('layouts.public')

@section('title', __('signup.page_title.plano'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('assets/css/signup-plans.css') }}">
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
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
        <a href="{{ route('signup.materias') }}" class="back-link">
            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
            {{ __('signup.back_materias') }}
        </a>
        <div class="signup-flow-topbar flex-shrink-0 ms-auto">
            <div class="navbar-actions navbar-actions--landing">
                <div class="navbar-actions__inner">
                    @include('components.language-selector')
                </div>
            </div>
        </div>
    </div>

    <div class="header-section">
        <h1>{{ __('signup.plano.h1') }}</h1>
        <p>{{ __('signup.plano.lead') }}</p>
    </div>

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

    <div class="plans-container">
        @foreach ($plans as $plan)
            <form method="POST" class="plan-form" action="{{ url()->current() }}">
                @csrf
                <article
                    class="plan-card{{ $plan['popular'] ? ' plan-card--popular' : '' }}"
                    aria-labelledby="plan-title-{{ $plan['id'] }}"
                >
                    <div class="plan-card__badge-row">
                        @if (!empty($plan['badge']))
                            <span class="plan-card__badge">{{ $plan['badge'] }}</span>
                        @endif
                    </div>

                    <header class="plan-card__header">
                        <h2 class="plan-card__title" id="plan-title-{{ $plan['id'] }}">{{ $plan['name'] }}</h2>
                        <p class="plan-card__lead">{{ $plan['description'] }}</p>
                    </header>

                    <section class="plan-card__pricing">
                        <div class="plan-card__price-row">
                            <span class="plan-card__currency">$</span>
                            <span class="plan-card__amount">{{ number_format($plan['price'] * count($materiasInfo), 2, ',', '.') }}</span>
                            <span class="plan-card__money">ARS</span>
                        </div>
                        <p class="plan-card__period">Total &middot; {{ $plan['duration'] }}</p>
                    </section>

                    <ul class="plan-card__features" role="list">
                        @foreach ($plan['features'] as $feature)
                            <li class="plan-card__feature">
                                <span class="plan-card__feature-icon" aria-hidden="true">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <footer class="plan-card__footer">
                        <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                        <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-2 plan-card__cta">
                            {{ __('signup.plano.choose') }}
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </footer>
                </article>
            </form>
        @endforeach
    </div>
</main>
@endsection
