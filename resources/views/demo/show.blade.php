@extends('layouts.public')

@section('title', __('demo.page_title'))
@section('body_attr')
 class="lp-body demo-body demo-body--funnel"
@endsection

@push('styles')
    @include('pages.partials.landing-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}?v={{ filemtime(public_path('assets/css/demo.css')) }}">
@endpush

@section('public_topbar')
    @include('pages.partials.topbar')
@endsection

@section('public_offcanvas')
    @include('pages.partials.offcanvas-public')
@endsection

@section('content')
    <section class="demo-pick-section lp-section">
        <div class="lp-container">
            <header class="lp-section__header text-center demo-pick-section__head">
                <span class="lp-badge">{{ __('demo.configurar.badge_demo') }}</span>
                <h1 class="lp-h-section">{{ __('demo.show.heading_objetivo') }}</h1>
                <p class="demo-pick-section__lead">{{ __('demo.show.subtitle_objetivo') }}</p>
            </header>

            @if(session('error'))
                <div class="alert alert-danger demo-pick-section__alert" role="alert">{{ session('error') }}</div>
            @endif

            <div class="demo-pick-grid">
                @forelse($faculdades as $fac)
                    @include('demo.partials.objetivo-card', ['faculdade' => $fac])
                @empty
                    <p class="demo-pick-grid__empty">{{ __('demo.questao.no_questions') }}</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
