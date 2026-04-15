@extends('layouts.public')

@section('title', __('signup.page_title.materias'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/top-bar-brand.css') }}">
<style>
    :root {
        --accent-purple: #6a0392;
        --accent-purple-lighter: rgba(106, 3, 146, 0.12);
    }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 45%, #460161 100%);
        background-size: 160% 160%;
        animation: floatBg 16s ease-in-out infinite;
        min-height: 100vh;
    }
    @keyframes floatBg {
        0%, 100% { background-position: 0% 40%; }
        50% { background-position: 100% 60%; }
    }
    .container-custom {
        max-width: 1100px;
        margin: 0 auto;
        padding: 1.75rem 1rem 3rem;
    }
    .header-section {
        text-align: center;
        margin-bottom: 2rem;
        color: #fff;
    }
    .header-section h1 {
        font-family: 'Poppins', sans-serif;
        font-weight: 800;
        font-size: clamp(1.75rem, 4vw, 2.5rem);
    }
    .materia-card {
        background: rgba(255,255,255,0.98);
        border-radius: 16px;
        border: 2px solid #e5e7eb;
        padding: 1.25rem;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        text-align: center;
    }
    .materia-card:hover {
        border-color: var(--accent-purple);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(106,3,146,0.15);
    }
    .materia-card.selected {
        border-color: var(--accent-purple);
        background: var(--accent-purple-lighter);
        box-shadow: 0 0 0 3px rgba(106,3,146,0.2);
    }
    .materia-card input[type="checkbox"] {
        display: none;
    }
    .materia-icon {
        font-size: 2rem;
        color: var(--accent-purple);
        margin-bottom: 0.5rem;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
    }
    .back-link:hover {
        color: rgba(255,255,255,0.8);
    }
    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255,255,255,0.2);
    }
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }
    .step.active .step-number {
        background: var(--accent-purple);
        box-shadow: 0 0 0 0.3rem rgba(106,3,146,0.12);
    }
    .step-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255,255,255,0.7);
        text-transform: uppercase;
    }
    .step.active .step-label {
        color: white;
    }
</style>
@endpush

@section('content')
<main class="container-custom">
    {{-- Top bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <a href="{{ route('home') }}" class="brand-mark" aria-label="Banco de Choices">
            <img src="{{ asset('img/logo-bd-transparente.png') }}" alt="Banco de Choices" width="180" height="40">
        </a>
        <div class="d-flex align-items-center gap-2">
            @include('components.language-selector')
            <a href="{{ route('home') }}" class="back-link">
                <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                {{ __('signup.back_home') }}
            </a>
        </div>
    </div>

    {{-- Header --}}
    <div class="header-section">
        <h1>{{ __('signup.materias.h1') }}</h1>
        <p class="opacity-90">{{ __('signup.materias.lead') }}</p>
    </div>

    {{-- Step indicator --}}
    <div class="step-indicator" aria-label="{{ __('signup.steps.aria') }}">
        <div class="step active" aria-current="step">
            <div class="step-number">1</div>
            <div class="step-label">{{ __('signup.step.materias') }}</div>
        </div>
        <div class="step">
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

    {{-- Error --}}
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Materia selection form --}}
    <form action="{{ route('signup.materias') }}" method="POST" id="materiasForm">
        @csrf
        <div class="row g-3 mb-4">
            @foreach ($materias as $materia)
                <div class="col-6 col-md-4 col-lg-3">
                    <label class="materia-card d-block h-100" id="card-{{ $materia->id }}">
                        <input type="checkbox" name="materias[]" value="{{ $materia->id }}"
                               onchange="this.closest('.materia-card').classList.toggle('selected', this.checked)">
                        <div class="materia-icon">
                            <i class="bi bi-journal-medical" aria-hidden="true"></i>
                        </div>
                        <h6 class="fw-bold mb-0 small">{{ $materia->nome }}</h6>
                    </label>
                </div>
            @endforeach
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-light btn-lg fw-bold px-5 py-3 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                {{ __('signup.materias.continue') }}
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </form>
</main>
@endsection
