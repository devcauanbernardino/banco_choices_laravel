@extends('layouts.app')

@section('title', __('addon.page_title_materias'))
@section('mobile_title', trim(explode('|', __('addon.page_title_materias'))[0]))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/page-comprar-materias-mock.css') }}">
@endpush

@section('content')
    <div class="bc-mock-addon bc-mock-page-shell">
        <header class="bc-mock-addon__hero">
            <h1 class="bc-mock-addon__title">{{ __('nav.buy_subjects') }}</h1>
            <p class="bc-mock-addon__lead">{{ __('addon.intro') }}</p>
        </header>

        @if (session('error'))
            <div class="alert alert-warning border-0 rounded-3 shadow-sm mb-4">{{ session('error') }}</div>
        @endif

        @if (!$temMateriasCompraveis)
            <div class="bc-card overflow-hidden">
                <div class="bc-empty-state">
                    <span class="material-icons text-success" aria-hidden="true">check_circle</span>
                    <h5 class="fw-bold mb-2">{{ __('addon.empty') }}</h5>
                    <p class="text-muted mb-0">{{ __('addon.empty_hint') }}</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg mt-4 px-4 rounded-3">
                        {{ __('nav.dashboard') }}
                    </a>
                </div>
            </div>
        @else
            <form method="post" action="{{ route('addon.materias') }}">
                @csrf
                <h2 class="bc-mock-addon__section-label">{{ __('addon.pick_section') }}</h2>
                @include('partials.catalog-materias-flow', [
                    'excludeIdsCsv' => $excludeOwnedCsv ?? '',
                    'presetMateriaId' => 0,
                ])
                <div class="bc-mock-addon__actions">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg bc-mock-addon__btn-primary w-100 w-sm-auto">{{ __('nav.dashboard') }}</a>
                    <button type="submit" class="btn btn-primary btn-lg bc-mock-addon__btn-primary w-100 w-sm-auto">{{ __('addon.continue_checkout') }}</button>
                </div>
            </form>
        @endif
    </div>
@endsection
