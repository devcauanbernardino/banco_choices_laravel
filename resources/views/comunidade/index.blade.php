@extends('layouts.app')

@section('title', __('comunidade.page_title'))
@section('mobile_title', __('comunidade.mobile_title'))
@section('topbar_title', __('comunidade.mobile_title'))

@push('styles')
<style>
.cm-header { margin-bottom: 22px; }
.cm-header h1 { font-size: clamp(1.4rem,2.2vw,1.7rem); font-weight: 700; color: var(--app-text); margin-bottom: 6px; }
.cm-header p { color: var(--app-muted); font-size: .9rem; margin: 0; }

.cm-card {
    border-radius: 18px;
    padding: 20px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border: 1px solid rgba(255,255,255,.5);
    box-shadow: 0 8px 28px rgba(31,10,60,.08);
    margin-bottom: 18px;
}
[data-theme="dark"] .cm-card { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); box-shadow: 0 8px 28px rgba(0,0,0,.35); }

.cm-composer textarea { resize: vertical; }
.cm-composer .cm-submit-row { display: flex; justify-content: flex-end; margin-top: 10px; }
.cm-btn { padding: 9px 18px; border-radius: 10px; border: none; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; font-size: .86rem; cursor: pointer; box-shadow: 0 6px 18px rgba(106,3,146,.3); }
.cm-btn:disabled { opacity: .5; cursor: default; }
.cm-btn--ghost { background: rgba(139,31,184,.1); color: #6a0392; box-shadow: none; border: 1px solid rgba(139,31,184,.25); }
.cm-btn--ghost:hover { background: rgba(139,31,184,.16); }
[data-theme="dark"] .cm-btn--ghost { background: rgba(199,125,253,.14); color: #e0bbfd; border-color: rgba(199,125,253,.3); }

.cm-post__head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
.cm-post__author { display: flex; align-items: center; gap: 10px; }
.cm-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg,#8b1fb8,#6a0392); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .9rem; }
.cm-post__name { font-weight: 700; color: var(--app-text); font-size: .92rem; margin: 0; }
.cm-post__time { color: var(--app-muted); font-size: .76rem; margin: 0; }
.cm-post__content { color: var(--app-text); font-size: .92rem; line-height: 1.55; white-space: pre-line; margin-bottom: 12px; }

.cm-post__actions { display: flex; align-items: center; gap: 14px; margin-bottom: 4px; }
.cm-action-link { background: none; border: none; padding: 0; color: var(--app-muted); font-size: .78rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
.cm-action-link:hover { color: #8b1fb8; }
.cm-action-link .material-symbols-outlined { font-size: 1rem; }
.cm-action-link--danger:hover { color: #dc3545; }

.cm-comments { margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(120,120,140,.15); }
[data-theme="dark"] .cm-comments { border-top-color: rgba(255,255,255,.1); }
.cm-comment { display: flex; gap: 8px; margin-bottom: 10px; }
.cm-comment__avatar { width: 28px; height: 28px; font-size: .74rem; }
.cm-comment__body { background: rgba(120,120,140,.08); border-radius: 12px; padding: 8px 12px; flex: 1; }
[data-theme="dark"] .cm-comment__body { background: rgba(255,255,255,.06); }
.cm-comment__name { font-weight: 700; font-size: .8rem; color: var(--app-text); margin: 0; }
.cm-comment__text { font-size: .85rem; color: var(--app-text); margin: 2px 0 0; white-space: pre-line; }
.cm-comment__actions { display: flex; gap: 10px; margin-top: 3px; }
.cm-comment__actions .cm-action-link { font-size: .72rem; }

.cm-comment-form { display: flex; gap: 8px; margin-top: 10px; }
.cm-comment-form input { flex: 1; }

.cm-empty { text-align: center; padding: 60px 20px; color: var(--app-muted); }
.cm-empty .material-symbols-outlined { font-size: 3rem; color: #8b1fb8; opacity: .5; margin-bottom: 12px; display: block; }

.cm-modal .modal-content { border-radius: 22px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.9); backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); box-shadow: 0 25px 60px rgba(31,10,60,.18); }
[data-theme="dark"] .cm-modal .modal-content { background: rgba(30,20,40,.92); border-color: rgba(255,255,255,.1); box-shadow: 0 25px 60px rgba(0,0,0,.55); }

.cm-pagination { display: flex; justify-content: center; margin-top: 8px; }

.cm-composer__toolbar { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
.cm-image-btn { display: inline-flex; align-items: center; gap: 6px; background: none; border: none; padding: 6px 4px; color: #8b1fb8; font-size: .82rem; font-weight: 600; cursor: pointer; }
[data-theme="dark"] .cm-image-btn { color: #c77dfd; }
.cm-image-btn:hover { text-decoration: underline; }

.cm-preview-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.cm-preview-item { position: relative; width: 72px; height: 72px; border-radius: 10px; overflow: hidden; border: 1px solid rgba(120,120,140,.2); }
[data-theme="dark"] .cm-preview-item { border-color: rgba(255,255,255,.14); }
.cm-preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cm-preview-item__remove { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; border: none; background: rgba(0,0,0,.6); color: #fff; font-size: .7rem; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; }

.cm-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 8px; margin-bottom: 12px; }
.cm-gallery a { display: block; border-radius: 12px; overflow: hidden; aspect-ratio: 1 / 1; border: 1px solid rgba(120,120,140,.15); }
[data-theme="dark"] .cm-gallery a { border-color: rgba(255,255,255,.1); }
.cm-gallery img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .18s ease; }
.cm-gallery a:hover img { transform: scale(1.04); }
</style>
@endpush

@section('content')
<div class="cm-header">
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

<div class="cm-card cm-composer">
    <form method="POST" action="{{ route('comunidade.store') }}" enctype="multipart/form-data" id="cmComposerForm">
        @csrf
        <textarea name="conteudo" class="form-control" rows="3" maxlength="2000" placeholder="{{ __('comunidade.form.placeholder') }}" required></textarea>
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

@if ($posts->isEmpty())
    <div class="cm-empty">
        <span class="material-symbols-outlined" aria-hidden="true">forum</span>
        <p class="mb-0">{{ __('comunidade.empty') }}</p>
    </div>
@else
    @foreach ($posts as $post)
        @php $iniciais = mb_strtoupper(mb_substr($post->usuario->nome ?? '?', 0, 1)); @endphp
        <div class="cm-card">
            <div class="cm-post__head">
                <div class="cm-post__author">
                    <div class="cm-avatar" aria-hidden="true">{{ $iniciais }}</div>
                    <div>
                        <p class="cm-post__name">{{ $post->usuario->nome ?? '—' }}</p>
                        <p class="cm-post__time">{{ $post->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
            <p class="cm-post__content">{{ $post->conteudo }}</p>
            @if ($post->imagens->isNotEmpty())
                <div class="cm-gallery">
                    @foreach ($post->imagens as $imagem)
                        <a href="{{ $imagem->url }}" target="_blank" rel="noopener">
                            <img src="{{ $imagem->url }}" alt="" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="cm-post__actions">
                <button type="button" class="cm-action-link" data-bs-toggle="collapse" data-bs-target="#cmComments{{ $post->id }}">
                    <span class="material-symbols-outlined" aria-hidden="true">chat_bubble</span>
                    {{ __('comunidade.post.comments_count', ['n' => $post->comentarios_count]) }}
                </button>
                @auth
                    @if ((int) $post->usuario_id === (int) auth()->id())
                        <form method="POST" action="{{ route('comunidade.destroy', $post) }}" onsubmit="return confirm(@json(__('comunidade.post.delete_confirm')));">
                            @csrf @method('DELETE')
                            <button type="submit" class="cm-action-link cm-action-link--danger">
                                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                {{ __('comunidade.post.delete') }}
                            </button>
                        </form>
                    @else
                        <button type="button" class="cm-action-link" data-bs-toggle="modal" data-bs-target="#cmReportModal" data-cm-report-action="{{ route('comunidade.denunciar', $post) }}">
                            <span class="material-symbols-outlined" aria-hidden="true">flag</span>
                            {{ __('comunidade.post.report') }}
                        </button>
                    @endif
                @endauth
            </div>

            <div class="collapse cm-comments" id="cmComments{{ $post->id }}">
                @foreach ($post->comentarios as $comentario)
                    @php $inicC = mb_strtoupper(mb_substr($comentario->usuario->nome ?? '?', 0, 1)); @endphp
                    <div class="cm-comment">
                        <div class="cm-avatar cm-comment__avatar" aria-hidden="true">{{ $inicC }}</div>
                        <div class="cm-comment__body">
                            <p class="cm-comment__name">{{ $comentario->usuario->nome ?? '—' }}</p>
                            <p class="cm-comment__text">{{ $comentario->conteudo }}</p>
                            <div class="cm-comment__actions">
                                <span class="cm-post__time">{{ $comentario->created_at->diffForHumans() }}</span>
                                @auth
                                    @if ((int) $comentario->usuario_id === (int) auth()->id())
                                        <form method="POST" action="{{ route('comunidade.comentarios.destroy', [$post, $comentario]) }}" onsubmit="return confirm(@json(__('comunidade.comment.delete_confirm')));">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="cm-action-link cm-action-link--danger">{{ __('comunidade.post.delete') }}</button>
                                        </form>
                                    @else
                                        <button type="button" class="cm-action-link" data-bs-toggle="modal" data-bs-target="#cmReportModal" data-cm-report-action="{{ route('comunidade.comentarios.denunciar', [$post, $comentario]) }}">
                                            {{ __('comunidade.post.report') }}
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
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
