@extends('layouts.app')

@section('title', __('addon.page_title_materias'))
@section('mobile_title', trim(explode('|', __('addon.page_title_materias'))[0]))
@section('topbar_title', __('nav.buy_subjects'))

@section('content')
    <div class="bc-page-header mb-4">
        <div>
            <h5 class="mb-0 fw-bold">{{ __('nav.buy_subjects') }}</h5>
            <small class="text-muted d-block mt-2">{{ __('addon.intro') }}</small>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    @if ($disponiveis->isEmpty())
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
            <div class="bc-card p-4 mb-4">
                <div class="list-group list-group-flush rounded-3 border">
                    @foreach ($disponiveis as $m)
                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <input class="form-check-input flex-shrink-0 mt-0" type="checkbox" name="materias[]" value="{{ $m->id }}">
                            <span class="material-icons text-primary flex-shrink-0">menu_book</span>
                            <span class="fw-semibold">{{ $m->nome }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="d-flex flex-column-reverse flex-sm-row gap-2 justify-content-sm-end align-items-stretch align-items-sm-center">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-lg w-100 w-sm-auto">{{ __('nav.dashboard') }}</a>
                <button type="submit" class="btn btn-primary btn-lg w-100 w-sm-auto">{{ __('addon.continue_checkout') }}</button>
            </div>
        </form>
    @endif
@endsection
