@extends('layouts.public')

@section('title', __('mascote.page_title'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', system-ui, sans-serif; background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 50%, #460161 100%); min-height: 100vh; }
    .mascote-wrap { max-width: 880px; margin: 3rem auto; padding: 0 1rem; }
    .mascote-header { text-align: center; color: #fff; margin-bottom: 2rem; }
    .mascote-header h1 { font-family: 'Poppins', sans-serif; font-weight: 800; }
    .mascote-header p { opacity: .85; }
    .mascote-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    .mascote-option { display: block; cursor: pointer; }
    .mascote-option input { position: absolute; opacity: 0; pointer-events: none; }
    .mascote-card { background: rgba(255,255,255,.97); border-radius: 20px; padding: 24px 18px; text-align: center; border: 3px solid transparent; transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease; }
    .mascote-card img { width: 140px; height: 140px; object-fit: contain; margin-bottom: 12px; }
    .mascote-card h3 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.05rem; margin-bottom: 4px; }
    .mascote-card p { font-size: .82rem; color: #6b7280; margin: 0; }
    .mascote-option:hover .mascote-card { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(0,0,0,.18); }
    .mascote-option input:checked + .mascote-card { border-color: #6a0392; box-shadow: 0 0 0 4px rgba(106,3,146,.15); }
    .mascote-submit-wrap { text-align: center; margin-top: 28px; }
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
                <button type="submit" class="btn btn-light btn-lg fw-bold px-5 py-3">{{ __('mascote.submit') }}</button>
            </div>
        </form>
    </div>
</main>
@endsection
