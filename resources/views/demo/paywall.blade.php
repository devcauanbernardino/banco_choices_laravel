@extends('layouts.public')

@section('title', __('demo.paywall.page_title'))

@section('body_attr')
 class="lp-body bg-light-subtle"
@endsection

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/landing-page.css') }}">
@endpush

@section('content')
<div class="container py-5" style="max-width: 640px;">
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="text-center mb-4">
        <span class="material-symbols-outlined text-primary mb-2" style="font-size: 3rem;">lock_clock</span>
        <h1 class="h3 fw-bold">{{ __('demo.paywall.heading') }}</h1>
        <p class="text-secondary">{{ __('demo.paywall.lead') }}</p>
    </div>

    <div class="d-grid gap-2">
        @php $signupUrl = route('signup.materias', array_filter(['materia_id' => $materiaPreId ?? null])); @endphp
        <a class="btn btn-primary btn-lg" href="{{ $signupUrl }}">{{ __('demo.paywall.cta_signup') }}</a>
        <a class="btn btn-outline-primary btn-lg" href="{{ route('login') }}">{{ __('demo.paywall.cta_login') }}</a>
        <a class="btn btn-outline-secondary" href="{{ route('demo.show') }}">{{ __('demo.paywall.cta_demo_again') }}</a>
        <a class="btn btn-link text-decoration-none" href="{{ route('home') }}">{{ __('demo.paywall.cta_home') }}</a>
    </div>
</div>
@endsection
