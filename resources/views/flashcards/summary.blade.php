@extends('layouts.app')

@section('title', __('flashcards.page_title'))
@section('mobile_title', __('flashcards.mobile_title'))
@section('topbar_title', __('flashcards.mobile_title'))

@push('styles')
<style>
.fc-sum { max-width: 520px; margin: 0 auto; text-align: center; padding: 24px 0; }
.fc-sum h1 { font-size: 1.4rem; font-weight: 700; color: var(--app-text); margin-bottom: 8px; }
.fc-sum p { color: var(--app-muted); font-size: .9rem; }
.fc-sum__total { font-size: 2.4rem; font-weight: 800; color: #a855f7; margin: 12px 0; }
.fc-sum__grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; margin: 20px 0; }
.fc-sum__cell { background: var(--app-surface); border: 1px solid var(--app-border); border-radius: 12px; padding: 14px 8px; }
.fc-sum__cell strong { display: block; font-size: 1.2rem; }
.fc-sum__cta { display: inline-flex; margin-top: 16px; padding: 11px 22px; border-radius: 12px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .88rem; text-decoration: none; }
</style>
@endpush

@section('content')
<div class="fc-sum">
    <h1>{{ __('flashcards.summary.title') }}</h1>
    <p>{{ $materiaNome }}</p>
    <div class="fc-sum__total">{{ __('flashcards.summary.total_reviewed', ['n' => $total]) }}</div>

    <div class="fc-sum__grid">
        <div class="fc-sum__cell"><strong style="color:#f87171;">{{ $contagem['again'] }}</strong>{{ __('flashcards.summary.breakdown_again') }}</div>
        <div class="fc-sum__cell"><strong style="color:#f97316;">{{ $contagem['hard'] }}</strong>{{ __('flashcards.summary.breakdown_hard') }}</div>
        <div class="fc-sum__cell"><strong style="color:#22c55e;">{{ $contagem['good'] }}</strong>{{ __('flashcards.summary.breakdown_good') }}</div>
        <div class="fc-sum__cell"><strong style="color:#38bdf8;">{{ $contagem['easy'] }}</strong>{{ __('flashcards.summary.breakdown_easy') }}</div>
    </div>

    <a href="{{ route('flashcards.index') }}" class="fc-sum__cta">{{ __('flashcards.summary.cta_back') }}</a>
</div>
@endsection
