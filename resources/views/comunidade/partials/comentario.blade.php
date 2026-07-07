@php
    $isReply = $isReply ?? false;
    $inicC = mb_strtoupper(mb_substr($comentario->usuario->nome ?? '?', 0, 1));
    $comentarioCurtido = $comentario->curtidas->isNotEmpty();
    $souDonoComentario = auth()->check() && (int) $comentario->usuario_id === (int) auth()->id();
@endphp
<div class="cm-comment @if ($isReply) cm-comment--reply @endif">
    <div class="cm-avatar cm-comment__avatar" aria-hidden="true">{{ $inicC }}</div>
    <div class="cm-comment__body">
        <p class="cm-comment__name">{{ $comentario->usuario->nome ?? '—' }}</p>
        <p class="cm-comment__text">{{ $comentario->conteudo }}</p>
        <div class="cm-comment__actions">
            <span class="cm-post__time">{{ $comentario->created_at->diffForHumans() }}</span>
            @auth
                <form method="POST" action="{{ route('comunidade.comentarios.curtir', [$post, $comentario]) }}" class="cm-inline-form cm-like-form">
                    @csrf
                    <button type="submit" class="cm-action-link @if ($comentarioCurtido) cm-action-link--liked @endif" aria-label="{{ __('comunidade.comment.likes_aria') }}" data-liked-class="cm-action-link--liked">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:.95rem;">{{ $comentarioCurtido ? 'favorite' : 'favorite_border' }}</span>
                        <span class="cm-like-count">{{ $comentario->curtidas_count }}</span>
                    </button>
                </form>
                @if (!$isReply)
                    <button type="button" class="cm-action-link" data-bs-toggle="collapse" data-bs-target="#cmReplyForm{{ $comentario->id }}">
                        {{ __('comunidade.comment.reply') }}
                    </button>
                @endif
                @if ($souDonoComentario)
                    <button type="button" class="cm-action-link" data-bs-toggle="collapse" data-bs-target="#cmEditComment{{ $comentario->id }}">
                        {{ __('comunidade.comment.edit') }}
                    </button>
                    <button type="button" class="cm-action-link cm-action-link--danger" data-bs-toggle="collapse" data-bs-target="#cmDeleteComment{{ $comentario->id }}">{{ __('comunidade.post.delete') }}</button>
                @else
                    <button type="button" class="cm-action-link" data-bs-toggle="modal" data-bs-target="#cmReportModal" data-cm-report-action="{{ route('comunidade.comentarios.denunciar', [$post, $comentario]) }}">
                        {{ __('comunidade.post.report') }}
                    </button>
                @endif
            @endauth
        </div>

        @if ($souDonoComentario)
            <div class="collapse cm-edit-form cm-edit-form--comment" id="cmEditComment{{ $comentario->id }}">
                <form method="POST" action="{{ route('comunidade.comentarios.update', [$post, $comentario]) }}">
                    @csrf
                    @method('PUT')
                    <input type="text" name="conteudo" class="form-control" maxlength="1000" value="{{ $comentario->conteudo }}" required>
                    <div class="cm-composer__toolbar" style="justify-content:flex-end;gap:8px;">
                        <button type="button" class="cm-btn cm-btn--ghost" data-bs-toggle="collapse" data-bs-target="#cmEditComment{{ $comentario->id }}">{{ __('comunidade.form.cancel') }}</button>
                        <button type="submit" class="cm-btn">{{ __('comunidade.form.save') }}</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($souDonoComentario)
            <div class="collapse cm-confirm-banner" id="cmDeleteComment{{ $comentario->id }}">
                <div class="cm-confirm-banner__inner">
                    <span>{{ __('comunidade.comment.delete_confirm') }}</span>
                    <div class="cm-confirm-banner__actions">
                        <button type="button" class="cm-btn cm-btn--ghost" data-bs-toggle="collapse" data-bs-target="#cmDeleteComment{{ $comentario->id }}">{{ __('comunidade.form.cancel') }}</button>
                        <form method="POST" action="{{ route('comunidade.comentarios.destroy', [$post, $comentario]) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="cm-btn cm-btn--danger">{{ __('comunidade.post.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if (!$isReply)
            <div class="collapse" id="cmReplyForm{{ $comentario->id }}">
                <form class="cm-comment-form" method="POST" action="{{ route('comunidade.comentar', $post) }}">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comentario->id }}">
                    <input type="text" name="conteudo" class="form-control" maxlength="1000" placeholder="{{ __('comunidade.comment.reply_placeholder') }}" required>
                    <button type="submit" class="cm-btn cm-btn--ghost">{{ __('comunidade.comment.reply_submit') }}</button>
                </form>
            </div>

            @if ($comentario->respostas->isNotEmpty())
                <div class="cm-replies">
                    @foreach ($comentario->respostas as $resposta)
                        @include('comunidade.partials.comentario', ['comentario' => $resposta, 'post' => $post, 'isReply' => true])
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
