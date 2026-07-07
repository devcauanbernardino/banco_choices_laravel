@extends('layouts.app')

@section('title', __('comunidade.page_title'))
@section('mobile_title', __('comunidade.mobile_title'))
@section('topbar_title', __('comunidade.mobile_title'))

@push('styles')
<style>
.cm-banner {
    position: relative;
    border-radius: 20px;
    padding: 28px 30px;
    margin-bottom: 22px;
    background: linear-gradient(135deg,#8b1fb8,#6a0392);
    color: #fff;
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(106,3,146,.25);
}
.cm-banner::before {
    content: '';
    position: absolute;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    top: -100px;
    right: -70px;
}
.cm-banner__icon {
    position: relative;
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: rgba(255,255,255,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}
.cm-banner__icon .material-symbols-outlined { font-size: 1.5rem; }
.cm-banner h1 { position: relative; font-size: clamp(1.4rem,2.4vw,1.85rem); font-weight: 800; margin-bottom: 6px; }
.cm-banner p { position: relative; opacity: .92; font-size: .92rem; margin: 0; max-width: 540px; }

.cm-layout { display: grid; grid-template-columns: minmax(0,1fr) 290px; gap: 22px; align-items: start; }
.cm-sidebar { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 88px; }
@media (max-width: 991px) {
    .cm-layout { grid-template-columns: 1fr; }
    .cm-feed { order: 1; }
    .cm-sidebar { order: 2; position: static; }
}

.cm-card {
    border-radius: 16px;
    padding: 14px 16px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
    margin-bottom: 14px;
}
[data-theme="dark"] .cm-card { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }

.cm-stat-card h3, .cm-guidelines h3, .cm-filter-card h3, .cm-sort-card h3 {
    font-size: .76rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--app-muted);
    font-weight: 800;
    margin-bottom: 14px;
}
.cm-stat-row { display: flex; align-items: baseline; justify-content: space-between; padding: 9px 0; }
.cm-stat-row + .cm-stat-row { border-top: 1px solid rgba(120,120,140,.12); }
[data-theme="dark"] .cm-stat-row + .cm-stat-row { border-top-color: rgba(255,255,255,.08); }
.cm-stat-value { font-size: 1.4rem; font-weight: 800; color: #8b1fb8; }
[data-theme="dark"] .cm-stat-value { color: #c77dfd; }
.cm-stat-label { font-size: .8rem; color: var(--app-muted); }

.cm-guidelines ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
.cm-guidelines li { display: flex; gap: 8px; font-size: .82rem; color: var(--app-text); line-height: 1.4; }
.cm-guidelines .material-symbols-outlined { font-size: 1.1rem; color: #8b1fb8; flex-shrink: 0; }
[data-theme="dark"] .cm-guidelines .material-symbols-outlined { color: #c77dfd; }

.cm-search-form { position: relative; }
.cm-search-form .material-symbols-outlined { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; color: var(--app-muted); pointer-events: none; }
.cm-search-form input { padding-left: 36px; border-radius: 10px; border: 1px solid rgba(120,120,140,.2); background: rgba(255,255,255,.6); }
[data-theme="dark"] .cm-search-form input { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.14); color: var(--app-text); }

.cm-filter-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px; }
.cm-filter-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 10px;
    text-decoration: none;
    color: var(--app-text);
    font-size: .82rem;
    font-weight: 600;
}
.cm-filter-item:hover { background: rgba(139,31,184,.08); color: var(--app-text); }
[data-theme="dark"] .cm-filter-item:hover { background: rgba(199,125,253,.1); }
.cm-filter-item.active { background: rgba(139,31,184,.14); color: #6a0392; }
[data-theme="dark"] .cm-filter-item.active { background: rgba(199,125,253,.18); color: #e0bbfd; }
.cm-filter-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; background: #8b1fb8; }
.cm-filter-item__label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cm-filter-item__count { font-size: .72rem; font-weight: 700; color: var(--app-muted); }

.cm-sort-toggle { display: flex; border-radius: 10px; background: rgba(120,120,140,.1); padding: 3px; gap: 3px; }
[data-theme="dark"] .cm-sort-toggle { background: rgba(255,255,255,.06); }
.cm-sort-toggle a {
    flex: 1;
    text-align: center;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    color: var(--app-muted);
    text-decoration: none;
}
.cm-sort-toggle a.active { background: #fff; color: #6a0392; box-shadow: 0 2px 8px rgba(31,10,60,.12); }
[data-theme="dark"] .cm-sort-toggle a.active { background: rgba(255,255,255,.12); color: #e0bbfd; }

.cm-composer { display: flex; gap: 12px; align-items: flex-start; }
.cm-composer form { flex: 1; min-width: 0; }
.cm-composer textarea,
.cm-title-input,
.cm-category-select {
    border-radius: 16px;
    border: 1px solid rgba(120,120,140,.2);
    background: rgba(255,255,255,.6);
}
.cm-title-input { border-radius: 12px; margin-bottom: 8px; font-weight: 700; }
.cm-category-select { border-radius: 12px; margin-bottom: 8px; }
.cm-composer textarea { resize: vertical; }
[data-theme="dark"] .cm-composer textarea,
[data-theme="dark"] .cm-title-input,
[data-theme="dark"] .cm-category-select { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.14); color: var(--app-text); }
.cm-composer .cm-submit-row { display: flex; justify-content: flex-end; margin-top: 10px; }
.cm-composer-locked { text-align: center; padding: 26px 20px; }
.cm-composer-locked .material-symbols-outlined { font-size: 2.2rem; color: #8b1fb8; opacity: .6; margin-bottom: 8px; display: block; }
.cm-composer-locked p { color: var(--app-muted); font-size: .85rem; margin: 0; }

.cm-btn { padding: 9px 18px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .86rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.cm-btn:disabled { opacity: .5; cursor: default; }
.cm-btn--ghost { background: rgba(139,31,184,.1); color: #6a0392; box-shadow: none; border: 1px solid rgba(139,31,184,.25); }
.cm-btn--ghost:hover { background: rgba(139,31,184,.16); }
[data-theme="dark"] .cm-btn--ghost { background: rgba(199,125,253,.14); color: #e0bbfd; border-color: rgba(199,125,253,.3); }

.cm-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .75rem; }

.cm-feed .cm-card,
.cm-post,
.cm-empty,
.cm-results-count {
    max-width: 720px;
    margin-inline: auto;
}

.cm-results-count { font-size: .78rem; color: var(--app-muted); margin: 0 0 10px; font-weight: 600; }

.cm-post {
    border-radius: 14px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
    margin-bottom: 10px;
    overflow: hidden;
}
[data-theme="dark"] .cm-post { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }
.cm-post__body { padding: 8px 12px; }

.cm-post__head { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 4px; }
.cm-post__author { display: flex; align-items: center; gap: 6px; }
.cm-post__name { font-weight: 700; color: var(--app-text); font-size: .8rem; margin: 0; }
.cm-post__time { color: var(--app-muted); font-size: .7rem; margin: 0; }
.cm-post__content { color: var(--app-text); font-size: .82rem; line-height: 1.4; white-space: pre-line; margin-bottom: 6px; }

.cm-category-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    margin-bottom: 6px;
}
.cm-post__title { font-size: .95rem; font-weight: 800; color: var(--app-text); margin: 0 0 4px; line-height: 1.3; }

.cm-post__actions {
    display: flex;
    border-top: 1px solid rgba(120,120,140,.12);
}
[data-theme="dark"] .cm-post__actions { border-top-color: rgba(255,255,255,.08); }
.cm-action-slot { flex: 1; display: flex; }
.cm-action-slot + .cm-action-slot { border-left: 1px solid rgba(120,120,140,.12); }
[data-theme="dark"] .cm-action-slot + .cm-action-slot { border-left-color: rgba(255,255,255,.08); }
.cm-action-slot form { flex: 1; display: flex; }
.cm-action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: none;
    border: none;
    outline-offset: -2px;
    padding: 10px 16px;
    color: var(--app-muted);
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
}
.cm-action-btn:hover { color: #8b1fb8; background: rgba(139,31,184,.06); }
[data-theme="dark"] .cm-action-btn:hover { color: #c77dfd; background: rgba(199,125,253,.08); }
.cm-action-btn:focus { outline: none; }
.cm-action-btn:focus-visible { outline: 2px solid rgba(139,31,184,.45); }
.cm-action-btn .material-symbols-outlined { font-size: 1.1rem; }
.cm-action-btn--danger:hover { color: #dc3545; background: rgba(220,53,69,.06); }
[data-theme="dark"] .cm-action-btn--danger:hover { color: #f87171; background: rgba(248,113,113,.1); }
.cm-action-btn--liked { color: #dc3545; }
.cm-action-btn--liked .material-symbols-outlined { font-variation-settings: 'FILL' 1; }

.cm-comments { padding: 14px 16px 16px; border-top: 1px solid rgba(120,120,140,.15); }
[data-theme="dark"] .cm-comments { border-top-color: rgba(255,255,255,.1); }
.cm-comment { display: flex; gap: 8px; margin-bottom: 8px; }
.cm-comment--reply { margin-top: 8px; margin-bottom: 0; }
.cm-comment__avatar { width: 26px; height: 26px; font-size: .7rem; }
.cm-comment__body { background: rgba(120,120,140,.08); border-radius: 12px; padding: 8px 12px; flex: 1; min-width: 0; }
[data-theme="dark"] .cm-comment__body { background: rgba(255,255,255,.06); }
.cm-comment__name { font-weight: 700; font-size: .8rem; color: var(--app-text); margin: 0; }
.cm-comment__text { font-size: .85rem; color: var(--app-text); margin: 2px 0 0; white-space: pre-line; }
.cm-comment__actions { display: flex; gap: 10px; margin-top: 3px; flex-wrap: wrap; }
.cm-action-link { background: none; border: none; padding: 0; color: var(--app-muted); font-size: .72rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
.cm-action-link:hover { color: #8b1fb8; }
.cm-action-link--danger:hover { color: #dc3545; }
.cm-action-link--liked { color: #dc3545; }
.cm-action-link--liked .material-symbols-outlined { font-variation-settings: 'FILL' 1; }
.cm-inline-form { display: inline-flex; }

.cm-replies { margin-left: 34px; margin-top: 4px; }

.cm-edit-form { margin-top: 8px; }
.cm-edit-form form { display: flex; flex-direction: column; gap: 8px; }
.cm-edit-form--comment form { flex-direction: row; align-items: center; }
.cm-edit-form--comment input[type="text"] { flex: 1; }

.cm-comment-form { display: flex; gap: 8px; margin-top: 10px; }
.cm-comment-form input { flex: 1; }

.cm-empty { text-align: center; padding: 60px 20px; color: var(--app-muted); }
.cm-empty .material-symbols-outlined { font-size: 3rem; color: #8b1fb8; opacity: .5; margin-bottom: 12px; display: block; }

.cm-modal .modal-content { border-radius: 22px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.9); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); box-shadow: 0 25px 60px rgba(31,10,60,.18); }
[data-theme="dark"] .cm-modal .modal-content { background: rgba(30,20,40,.92); border-color: rgba(255,255,255,.1); box-shadow: 0 25px 60px rgba(0,0,0,.55); }

.cm-image-modal .modal-dialog { display: flex; align-items: center; justify-content: center; height: 100%; max-width: min(90vw, 900px); }
.cm-image-modal .modal-content { background: none; border: none; box-shadow: none; position: relative; }
.cm-image-modal img { width: 100%; max-height: 85vh; object-fit: contain; border-radius: 12px; display: block; }
.cm-image-modal__close {
    position: absolute;
    top: -44px;
    right: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(0,0,0,.5);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.cm-image-modal__close:hover { background: rgba(0,0,0,.7); }

.cm-pagination { display: flex; justify-content: center; margin-top: 8px; }

.cm-composer__toolbar { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
.cm-image-btn { display: inline-flex; align-items: center; gap: 6px; background: none; border: none; border-radius: 8px; padding: 6px 10px; margin-left: -10px; color: #8b1fb8; font-size: .82rem; font-weight: 600; cursor: pointer; }
[data-theme="dark"] .cm-image-btn { color: #c77dfd; }
.cm-image-btn:hover { background: rgba(139,31,184,.08); }
[data-theme="dark"] .cm-image-btn:hover { background: rgba(199,125,253,.1); }
.cm-image-btn:focus { outline: none; }
.cm-image-btn:focus-visible { outline: 2px solid rgba(139,31,184,.45); }

.cm-preview-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.cm-preview-item { position: relative; width: 72px; height: 72px; border-radius: 10px; overflow: hidden; border: 1px solid rgba(120,120,140,.2); }
[data-theme="dark"] .cm-preview-item { border-color: rgba(255,255,255,.14); }
.cm-preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cm-preview-item__remove { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; border: none; background: rgba(0,0,0,.6); color: #fff; font-size: .7rem; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; }

.cm-gallery {
    display: grid;
    gap: 3px;
    margin-bottom: 8px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(120,120,140,.15);
}
[data-theme="dark"] .cm-gallery { border-color: rgba(255,255,255,.1); }
.cm-gallery button { display: block; overflow: hidden; position: relative; width: 100%; padding: 0; border: none; background: none; cursor: pointer; }
.cm-gallery img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .18s ease; }
.cm-gallery button:hover img { transform: scale(1.03); }

.cm-gallery[data-count="1"] { grid-template-columns: 1fr; aspect-ratio: 3 / 1; max-width: 320px; }

.cm-gallery[data-count="2"] { grid-template-columns: 1fr 1fr; aspect-ratio: 16 / 8; }

.cm-gallery[data-count="3"] { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; aspect-ratio: 4 / 3; }
.cm-gallery[data-count="3"] a:first-child { grid-row: span 2; }

.cm-gallery[data-count="4"] { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; aspect-ratio: 1 / 1; }

.cm-gallery__more::after {
    content: attr(data-extra);
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.45);
    color: #fff;
    font-weight: 700;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="cm-banner">
    <div class="cm-banner__icon"><span class="material-symbols-outlined" aria-hidden="true">forum</span></div>
    <h1>{{ __('comunidade.header.title') }}</h1>
    <p>{{ __('comunidade.header.sub') }}</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="cm-layout">
    <div class="cm-feed">
        @php $euIniciais = mb_strtoupper(mb_substr(auth()->user()->nome ?? '?', 0, 1)); @endphp

        @if ($materiasDoUsuario->isEmpty())
            <div class="cm-card cm-composer-locked">
                <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                <p>{{ __('comunidade.form.no_subjects') }}</p>
            </div>
        @else
            <div class="cm-card cm-composer">
                <div class="cm-avatar" aria-hidden="true">{{ $euIniciais }}</div>
                <form method="POST" action="{{ route('comunidade.store') }}" enctype="multipart/form-data" id="cmComposerForm">
                    @csrf
                    <input type="text" name="titulo" class="form-control cm-title-input" maxlength="180" placeholder="{{ __('comunidade.form.title_placeholder') }}" value="{{ old('titulo') }}" required>
                    <select name="materia_id" class="form-select cm-category-select" required>
                        <option value="" disabled @selected(old('materia_id') === null)>{{ __('comunidade.form.category_placeholder') }}</option>
                        @foreach ($materiasDoUsuario as $materia)
                            <option value="{{ $materia->id }}" @selected((string) old('materia_id') === (string) $materia->id)>{{ $materia->nome }}</option>
                        @endforeach
                    </select>
                    <textarea name="conteudo" class="form-control" rows="3" maxlength="2000" placeholder="{{ __('comunidade.form.placeholder') }}" required>{{ old('conteudo') }}</textarea>
                    <input type="file" name="imagens[]" id="cmComposerFile" accept="image/png,image/jpeg,image/webp,image/gif" multiple hidden>
                    <div class="cm-preview-grid" id="cmComposerPreview"></div>
                    <div class="cm-composer__toolbar">
                        <button type="button" class="cm-image-btn" id="cmComposerImageBtn">
                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1.1rem;">image</span>
                            {{ __('comunidade.form.add_image') }}
                        </button>
                        <button type="submit" class="cm-btn">{{ __('comunidade.form.submit') }}</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($materiaFiltro || $busca !== '')
            <p class="cm-results-count">{{ __('comunidade.results_count', ['n' => $posts->total()]) }}</p>
        @endif

        @if ($posts->isEmpty())
            <div class="cm-empty">
                <span class="material-symbols-outlined" aria-hidden="true">forum</span>
                <p class="mb-0">{{ ($materiaFiltro || $busca !== '') ? __('comunidade.empty_filtered') : __('comunidade.empty') }}</p>
            </div>
        @else
            @foreach ($posts as $post)
                @php
                    $iniciais = mb_strtoupper(mb_substr($post->usuario->nome ?? '?', 0, 1));
                    $postCurtido = $post->curtidas->isNotEmpty();
                    $souDonoPost = auth()->check() && (int) $post->usuario_id === (int) auth()->id();
                @endphp
                <div class="cm-post">
                    <div class="cm-post__body">
                        <div class="cm-post__head">
                            <div class="cm-post__author">
                                <div class="cm-avatar" aria-hidden="true">{{ $iniciais }}</div>
                                <div>
                                    <p class="cm-post__name">{{ $post->usuario->nome ?? '—' }}</p>
                                    <p class="cm-post__time">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($post->materia)
                            @php $corCat = $post->materia->corExibicao(); @endphp
                            <span class="cm-category-badge" style="background: {{ $corCat }}1f; color: {{ $corCat }};">{{ $post->materia->nome }}</span>
                        @endif
                        @if ($post->titulo)
                            <h2 class="cm-post__title">{{ $post->titulo }}</h2>
                        @endif

                        <p class="cm-post__content">{{ $post->conteudo }}</p>
                        @if ($post->imagens->isNotEmpty())
                            @php
                                $imagensVisiveis = $post->imagens->take(4);
                                $imagensExtras = max(0, $post->imagens->count() - 4);
                            @endphp
                            <div class="cm-gallery" data-count="{{ $imagensVisiveis->count() }}">
                                @foreach ($imagensVisiveis as $i => $imagem)
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#cmImageModal" data-image-url="{{ $imagem->url }}"
                                       class="{{ $i === 3 && $imagensExtras > 0 ? 'cm-gallery__more' : '' }}"
                                       @if ($i === 3 && $imagensExtras > 0) data-extra="+{{ $imagensExtras }}" @endif>
                                        <img src="{{ $imagem->url }}" alt="" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if ($souDonoPost)
                            <div class="collapse cm-edit-form" id="cmEditPost{{ $post->id }}">
                                <form method="POST" action="{{ route('comunidade.update', $post) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="titulo" class="form-control cm-title-input" maxlength="180" value="{{ $post->titulo }}" required>
                                    <select name="materia_id" class="form-select cm-category-select" required>
                                        @foreach ($materiasDoUsuario as $materia)
                                            <option value="{{ $materia->id }}" @selected((int) $post->materia_id === (int) $materia->id)>{{ $materia->nome }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="conteudo" class="form-control" rows="3" maxlength="2000" required>{{ $post->conteudo }}</textarea>
                                    <div class="cm-composer__toolbar" style="justify-content:flex-end;gap:8px;">
                                        <button type="button" class="cm-btn cm-btn--ghost" data-bs-toggle="collapse" data-bs-target="#cmEditPost{{ $post->id }}">{{ __('comunidade.form.cancel') }}</button>
                                        <button type="submit" class="cm-btn">{{ __('comunidade.form.save') }}</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="cm-post__actions">
                        @auth
                            <div class="cm-action-slot">
                                <form method="POST" action="{{ route('comunidade.curtir', $post) }}">
                                    @csrf
                                    <button type="submit" class="cm-action-btn @if ($postCurtido) cm-action-btn--liked @endif" aria-label="{{ __('comunidade.post.likes_aria') }}">
                                        <span class="material-symbols-outlined" aria-hidden="true">{{ $postCurtido ? 'favorite' : 'favorite_border' }}</span>
                                        {{ $post->curtidas_count }}
                                    </button>
                                </form>
                            </div>
                        @endauth
                        <div class="cm-action-slot">
                            <button type="button" class="cm-action-btn" data-bs-toggle="collapse" data-bs-target="#cmComments{{ $post->id }}">
                                <span class="material-symbols-outlined" aria-hidden="true">chat_bubble</span>
                                {{ __('comunidade.post.comments_count', ['n' => $post->todos_comentarios_count]) }}
                            </button>
                        </div>
                        @auth
                            @if ($souDonoPost)
                                <div class="cm-action-slot">
                                    <button type="button" class="cm-action-btn" data-bs-toggle="collapse" data-bs-target="#cmEditPost{{ $post->id }}">
                                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                        {{ __('comunidade.post.edit') }}
                                    </button>
                                </div>
                                <div class="cm-action-slot">
                                    <form method="POST" action="{{ route('comunidade.destroy', $post) }}" onsubmit="return confirm(@json(__('comunidade.post.delete_confirm')));">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="cm-action-btn cm-action-btn--danger">
                                            <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                            {{ __('comunidade.post.delete') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="cm-action-slot">
                                    <button type="button" class="cm-action-btn" data-bs-toggle="modal" data-bs-target="#cmReportModal" data-cm-report-action="{{ route('comunidade.denunciar', $post) }}">
                                        <span class="material-symbols-outlined" aria-hidden="true">flag</span>
                                        {{ __('comunidade.post.report') }}
                                    </button>
                                </div>
                            @endif
                        @endauth
                    </div>

                    <div class="collapse cm-comments" id="cmComments{{ $post->id }}">
                        @foreach ($post->comentarios as $comentario)
                            @include('comunidade.partials.comentario', ['comentario' => $comentario, 'post' => $post, 'isReply' => false])
                        @endforeach

                        <form class="cm-comment-form" method="POST" action="{{ route('comunidade.comentar', $post) }}">
                            @csrf
                            <input type="text" name="conteudo" class="form-control" maxlength="1000" placeholder="{{ __('comunidade.post.comment_placeholder') }}" required>
                            <button type="submit" class="cm-btn cm-btn--ghost">{{ __('comunidade.post.comment_submit') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach

            <div class="cm-pagination">
                {{ $posts->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <div class="cm-sidebar">
        <div class="cm-card">
            <form method="GET" action="{{ route('comunidade.index') }}" class="cm-search-form">
                @if ($materiaFiltro)
                    <input type="hidden" name="materia" value="{{ $materiaFiltro }}">
                @endif
                @if ($ordenacao !== 'recent')
                    <input type="hidden" name="sort" value="{{ $ordenacao }}">
                @endif
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input type="search" name="busca" class="form-control" value="{{ $busca }}" placeholder="{{ __('comunidade.search.placeholder') }}">
            </form>
        </div>

        <div class="cm-card cm-filter-card">
            <h3>{{ __('comunidade.sidebar.categories_title') }}</h3>
            <ul class="cm-filter-list">
                <li>
                    <a href="{{ route('comunidade.index', array_filter(['sort' => $ordenacao !== 'recent' ? $ordenacao : null, 'busca' => $busca !== '' ? $busca : null])) }}"
                       class="cm-filter-item @if (!$materiaFiltro) active @endif">
                        <span class="cm-filter-item__label">{{ __('comunidade.sidebar.category_all') }}</span>
                    </a>
                </li>
                @foreach ($categorias as $categoria)
                    <li>
                        <a href="{{ route('comunidade.index', array_filter(['materia' => $categoria->id, 'sort' => $ordenacao !== 'recent' ? $ordenacao : null, 'busca' => $busca !== '' ? $busca : null])) }}"
                           class="cm-filter-item @if ((int) $materiaFiltro === (int) $categoria->id) active @endif">
                            <span class="cm-filter-dot" style="background: {{ $categoria->corExibicao() }};"></span>
                            <span class="cm-filter-item__label">{{ $categoria->nome }}</span>
                            <span class="cm-filter-item__count">{{ $categoria->posts_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="cm-card cm-sort-card">
            <h3>{{ __('comunidade.sidebar.sort_title') }}</h3>
            <div class="cm-sort-toggle">
                <a href="{{ route('comunidade.index', array_filter(['materia' => $materiaFiltro, 'busca' => $busca !== '' ? $busca : null])) }}" class="@if ($ordenacao === 'recent') active @endif">
                    {{ __('comunidade.sidebar.sort_recent') }}
                </a>
                <a href="{{ route('comunidade.index', array_filter(['materia' => $materiaFiltro, 'sort' => 'popular', 'busca' => $busca !== '' ? $busca : null])) }}" class="@if ($ordenacao === 'popular') active @endif">
                    {{ __('comunidade.sidebar.sort_popular') }}
                </a>
            </div>
        </div>

        <div class="cm-card cm-stat-card">
            <h3>{{ __('comunidade.sidebar.stats_title') }}</h3>
            <div class="cm-stat-row">
                <span class="cm-stat-value">{{ $totalPosts }}</span>
                <span class="cm-stat-label">{{ __('comunidade.sidebar.posts_label') }}</span>
            </div>
            <div class="cm-stat-row">
                <span class="cm-stat-value">{{ $totalComentarios }}</span>
                <span class="cm-stat-label">{{ __('comunidade.sidebar.comments_label') }}</span>
            </div>
            <div class="cm-stat-row">
                <span class="cm-stat-value">{{ $totalParticipantes }}</span>
                <span class="cm-stat-label">{{ __('comunidade.sidebar.participants_label') }}</span>
            </div>
        </div>

        <div class="cm-card cm-guidelines">
            <h3>{{ __('comunidade.sidebar.guidelines_title') }}</h3>
            <ul>
                <li><span class="material-symbols-outlined" aria-hidden="true">school</span> {{ __('comunidade.sidebar.guideline_1') }}</li>
                <li><span class="material-symbols-outlined" aria-hidden="true">favorite</span> {{ __('comunidade.sidebar.guideline_2') }}</li>
                <li><span class="material-symbols-outlined" aria-hidden="true">flag</span> {{ __('comunidade.sidebar.guideline_3') }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade cm-modal" id="cmReportModal" tabindex="-1" aria-labelledby="cmReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h2 class="h5 fw-bold mb-3">{{ __('comunidade.report.title') }}</h2>
                <form method="POST" action="" id="cmReportForm">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">{{ __('comunidade.report.reason_label') }}</label>
                        <textarea name="motivo" class="form-control" rows="2" maxlength="255"></textarea>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="cm-btn cm-btn--ghost" data-bs-dismiss="modal">{{ __('comunidade.report.cancel') }}</button>
                        <button type="submit" class="cm-btn">{{ __('comunidade.report.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade cm-image-modal" id="cmImageModal" tabindex="-1" aria-labelledby="cmImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <button type="button" class="cm-image-modal__close" data-bs-dismiss="modal" aria-label="Close">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
            <img src="" alt="" id="cmImageModalImg">
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('cmReportModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (ev) {
        var trigger = ev.relatedTarget;
        var action = trigger ? trigger.getAttribute('data-cm-report-action') : null;
        var form = document.getElementById('cmReportForm');
        if (action && form) form.setAttribute('action', action);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var imageModal = document.getElementById('cmImageModal');
    if (!imageModal) return;
    var img = document.getElementById('cmImageModalImg');
    imageModal.addEventListener('show.bs.modal', function (ev) {
        var trigger = ev.relatedTarget;
        var url = trigger ? trigger.getAttribute('data-image-url') : null;
        if (url && img) img.src = url;
    });
    imageModal.addEventListener('hidden.bs.modal', function () {
        if (img) img.src = '';
    });
});

(function () {
    var MAX_IMAGES = {{ \App\Http\Controllers\ComunidadeController::MAX_IMAGENS_POR_POST }};
    var fileInput = document.getElementById('cmComposerFile');
    var imageBtn = document.getElementById('cmComposerImageBtn');
    var preview = document.getElementById('cmComposerPreview');
    if (!fileInput || !imageBtn || !preview) return;

    imageBtn.addEventListener('click', function () { fileInput.click(); });

    fileInput.addEventListener('change', function () {
        if (fileInput.files.length > MAX_IMAGES) {
            var dt = new DataTransfer();
            Array.from(fileInput.files).slice(0, MAX_IMAGES).forEach(function (f) { dt.items.add(f); });
            fileInput.files = dt.files;
        }
        renderPreview();
    });

    function renderPreview() {
        preview.innerHTML = '';
        Array.from(fileInput.files).forEach(function (file, idx) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var item = document.createElement('div');
                item.className = 'cm-preview-item';

                var img = document.createElement('img');
                img.src = e.target.result;

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'cm-preview-item__remove';
                removeBtn.setAttribute('aria-label', 'x');
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function () { removeFile(idx); });

                item.appendChild(img);
                item.appendChild(removeBtn);
                preview.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(index) {
        var dt = new DataTransfer();
        Array.from(fileInput.files).forEach(function (file, i) {
            if (i !== index) dt.items.add(file);
        });
        fileInput.files = dt.files;
        renderPreview();
    }

    var form = document.getElementById('cmComposerForm');
    if (form) {
        form.addEventListener('submit', function () { imageBtn.disabled = true; });
    }
})();
</script>
@endpush
