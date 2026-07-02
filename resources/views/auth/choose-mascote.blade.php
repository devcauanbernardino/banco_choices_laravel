@extends('layouts.public')

@section('title', __('mascote.page_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
<style>
    html, body { overflow-x: hidden; }
    body { font-family: 'Inter', system-ui, sans-serif; background: var(--app-bg); min-height: 100vh; position: relative; isolation: isolate; display: flex; flex-direction: column; }
    body::before, body::after { content: ''; position: fixed; width: 420px; height: 420px; border-radius: 50%; filter: blur(100px); z-index: -1; pointer-events: none; opacity: .35; }
    body::before { background: #8b1fb8; top: 6%; left: 6%; }
    body::after { background: #38bdf8; bottom: 4%; right: 8%; }
    [data-theme="dark"] body::before, [data-theme="dark"] body::after { opacity: .3; }

    main.container-fluid { flex: 1; display: flex; align-items: center; justify-content: center; padding-top: 2.5rem; padding-bottom: 2.5rem; }

    .mascote-wrap { max-width: 880px; width: 100%; margin: 0 auto; padding: 0 1rem; position: relative; z-index: 1; }
    .mascote-header { text-align: center; color: var(--app-text); margin-bottom: 2rem; }
    .mascote-header h1 { font-family: 'Poppins', sans-serif; font-weight: 800; }
    .mascote-header p { color: var(--app-muted); }
    .mascote-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); align-items: stretch; gap: 20px; }
    .mascote-option { display: block; cursor: pointer; height: 100%; }
    .mascote-option input { position: absolute; opacity: 0; pointer-events: none; }
    .mascote-card { background: rgba(255,255,255,.5); backdrop-filter: blur(18px) saturate(180%); -webkit-backdrop-filter: blur(18px) saturate(180%); border: 1px solid rgba(255,255,255,.4); box-shadow: 0 8px 32px rgba(31,10,60,.1); border-radius: 20px; padding: 24px 18px; text-align: center; transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease; height: 100%; min-height: 270px; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    [data-theme="dark"] .mascote-card { background: rgba(255,255,255,.055); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 32px rgba(0,0,0,.35); }
    .mascote-card img { width: 140px; height: 140px; object-fit: contain; margin-bottom: 12px; }
    .mascote-card h3 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.05rem; margin-bottom: 4px; color: var(--app-text); width: 100%; text-align: center; }
    .mascote-card p { font-size: .82rem; color: var(--app-muted); margin: 0; width: 100%; text-align: center; }
    .mascote-option:hover .mascote-card { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(0,0,0,.18); }
    .mascote-option input:checked + .mascote-card { border-color: #8b1fb8; box-shadow: 0 0 0 4px rgba(139,31,184,.18); }
    .mascote-submit-wrap { text-align: center; margin-top: 28px; }
    .mascote-submit-wrap .btn { background: linear-gradient(135deg,#8b1fb8,#6a0392); border: none; color: #fff; box-shadow: 0 8px 22px rgba(106,3,146,.35); }
</style>
@endpush

@section('content')
<main class="container-fluid">
    <div class="mascote-wrap">
        <div class="mascote-header">
            <h1 class="h3">{{ __('mascote.page_title') }}</h1>
            <p>{{ __('mascote.page_subtitle') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('mascote.store') }}">
            @csrf
            <div class="mascote-grid">
                <label class="mascote-option">
                    <input type="radio" name="mascote" value="robo" required>
                    <div class="mascote-card">
                        <img src="{{ asset('assets/img/mascots/robo-choice.png') }}" alt="{{ __('mascote.robo.nome') }}">
                        <h3>{{ __('mascote.robo.nome') }}</h3>
                        <p>{{ __('mascote.robo.desc') }}</p>
                    </div>
                </label>
                <label class="mascote-option">
                    <input type="radio" name="mascote" value="fantasma" required>
                    <div class="mascote-card">
                        <img src="{{ asset('assets/img/mascots/fantasma-choice.png') }}" alt="{{ __('mascote.fantasma.nome') }}">
                        <h3>{{ __('mascote.fantasma.nome') }}</h3>
                        <p>{{ __('mascote.fantasma.desc') }}</p>
                    </div>
                </label>
                <label class="mascote-option">
                    <input type="radio" name="mascote" value="gato" required>
                    <div class="mascote-card">
                        <img src="{{ asset('assets/img/mascots/gato-choice.png') }}" alt="{{ __('mascote.gato.nome') }}">
                        <h3>{{ __('mascote.gato.nome') }}</h3>
                        <p>{{ __('mascote.gato.desc') }}</p>
                    </div>
                </label>
            </div>

            <div class="mascote-submit-wrap">
                <button type="submit" class="btn btn-lg fw-bold px-5 py-3">{{ __('mascote.submit') }}</button>
            </div>
        </form>
    </div>
</main>
@endsection
