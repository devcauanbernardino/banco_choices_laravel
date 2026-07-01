@extends('layouts.app')

@section('title', __('flashcards.page_title'))
@section('mobile_title', __('flashcards.mobile_title'))
@section('topbar_title', __('flashcards.mobile_title'))

@push('styles')
<style>
.fc-header { margin-bottom: 24px; }
.fc-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.fc-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }

.fc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }

.fc-card { background: var(--app-surface); border: 1px solid var(--app-border); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.fc-card__title { font-size: 1rem; font-weight: 700; color: var(--app-text); margin: 0; }
.fc-card__badges { display: flex; gap: 8px; flex-wrap: wrap; }
.fc-badge { font-size: .74rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; }
.fc-badge--due { background: rgba(139,31,184,.12); color: #8b1fb8; }
.fc-badge--new { background: rgba(34,197,94,.12); color: #16a34a; }
.fc-badge--empty { background: var(--app-border); color: var(--app-muted); }
.fc-card__actions { margin-top: auto; display: flex; align-items: center; gap: 10px; }
.fc-card__input { width: 72px; border: 1px solid var(--app-border); border-radius: 8px; padding: 6px 8px; font-size: .82rem; background: var(--app-bg); color: var(--app-text); }
.fc-card__btn { flex: 1; padding: 9px 14px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .84rem; cursor: pointer; }
.fc-card__btn:disabled { opacity: .45; cursor: default; }
</style>
@endpush

@section('content')
<div class="fc-header">
    <h1>{{ __('flashcards.header.title') }}</h1>
    <p>{{ __('flashcards.header.sub') }}</p>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

@if ($materias->isEmpty())
    <p class="text-muted">{{ __('flashcards.no_subjects') }}</p>
@else
    <div class="fc-grid">
        @foreach ($materias as $m)
            @php $resumo = $resumoPorMateria[$m->id] ?? ['due_count' => 0, 'new_count' => 0, 'new_available_count' => 0]; @endphp
            <div class="fc-card">
                <h3 class="fc-card__title">{{ $m->nome }}</h3>
                <div class="fc-card__badges">
                    @if ($resumo['due_count'] > 0)
                        <span class="fc-badge fc-badge--due">{{ __('flashcards.card.due_count', ['n' => $resumo['due_count']]) }}</span>
                    @endif
                    @if ($resumo['new_available_count'] > 0)
                        <span class="fc-badge fc-badge--new">{{ __('flashcards.card.new_count', ['n' => $resumo['new_available_count']]) }}</span>
                    @endif
                    @if ($resumo['due_count'] === 0 && $resumo['new_available_count'] === 0)
                        <span class="fc-badge fc-badge--empty">{{ __('flashcards.card.all_caught_up') }}</span>
                    @endif
                </div>
                <form action="{{ route('flashcards.create') }}" method="post" class="fc-card__actions">
                    @csrf
                    <input type="hidden" name="materia" value="{{ $m->id }}">
                    <input type="number" name="novos_por_dia" class="fc-card__input" min="0" max="200" value="20"
                           aria-label="{{ __('flashcards.form.new_per_day_label') }}">
                    <button type="submit" class="fc-card__btn"
                            @if ($resumo['due_count'] === 0 && $resumo['new_available_count'] === 0) disabled @endif>
                        {{ __('flashcards.form.start') }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection
