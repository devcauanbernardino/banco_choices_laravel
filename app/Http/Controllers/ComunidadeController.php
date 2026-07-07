<?php

namespace App\Http\Controllers;

use App\Models\ComunidadeComentario;
use App\Models\ComunidadeDenuncia;
use App\Models\ComunidadePost;
use App\Models\ComunidadePostImagem;
use App\Models\Materia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ComunidadeController extends Controller
{
    public const MAX_IMAGENS_POR_POST = 6;

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $materiaFiltro = $request->integer('materia') ?: null;
        $ordenacao = $request->string('sort')->value() === 'popular' ? 'popular' : 'recent';
        $busca = trim((string) $request->string('busca'));

        $postsQuery = ComunidadePost::query()
            ->with([
                'usuario:id,nome',
                'materia:id,nome,cor',
                'imagens',
                'curtidas' => fn ($q) => $q->where('users.id', $user->id),
                'comentarios' => function ($query) use ($user) {
                    $query->withCount('curtidas')
                        ->with([
                            'usuario:id,nome',
                            'curtidas' => fn ($q) => $q->where('users.id', $user->id),
                            'respostas' => function ($query) use ($user) {
                                $query->withCount('curtidas')
                                    ->with([
                                        'usuario:id,nome',
                                        'curtidas' => fn ($q) => $q->where('users.id', $user->id),
                                    ]);
                            },
                        ]);
                },
            ])
            ->withCount(['curtidas', 'todosComentarios']);

        if ($materiaFiltro) {
            $postsQuery->where('materia_id', $materiaFiltro);
        }

        if ($busca !== '') {
            $postsQuery->where(function ($q) use ($busca) {
                $q->where('titulo', 'like', "%{$busca}%")
                    ->orWhere('conteudo', 'like', "%{$busca}%");
            });
        }

        if ($ordenacao === 'popular') {
            $postsQuery->orderByDesc('curtidas_count')->orderByDesc('created_at');
        } else {
            $postsQuery->orderByDesc('created_at');
        }

        $posts = $postsQuery->paginate(15)->withQueryString();

        $totalPosts = ComunidadePost::query()->count();
        $totalComentarios = ComunidadeComentario::query()->count();
        $totalParticipantes = ComunidadePost::query()->pluck('usuario_id')
            ->merge(ComunidadeComentario::query()->pluck('usuario_id'))
            ->unique()
            ->count();

        $categorias = Materia::query()
            ->withCount(['posts as posts_count'])
            ->whereHas('posts')
            ->orderBy('nome')
            ->get(['id', 'nome', 'cor']);

        $materiasDoUsuario = $user->materiasUnicas();

        return view('comunidade.index', compact(
            'posts',
            'totalPosts',
            'totalComentarios',
            'totalParticipantes',
            'categorias',
            'materiasDoUsuario',
            'materiaFiltro',
            'ordenacao',
            'busca'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaIds = $user->materiasUnicas()->pluck('id');

        $data = $request->validate([
            'titulo' => 'required|string|max:180',
            'materia_id' => ['required', 'integer', Rule::in($materiaIds)],
            'conteudo' => 'required|string|max:2000',
            'imagens' => 'nullable|array|max:'.self::MAX_IMAGENS_POR_POST,
            'imagens.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $post = ComunidadePost::create([
            'usuario_id' => $user->id,
            'titulo' => $data['titulo'],
            'materia_id' => $data['materia_id'],
            'conteudo' => $data['conteudo'],
        ]);

        foreach ($request->file('imagens', []) as $ordem => $arquivo) {
            $caminho = $arquivo->store('comunidade', 'public');
            ComunidadePostImagem::create([
                'post_id' => $post->id,
                'caminho' => $caminho,
                'ordem' => $ordem,
            ]);
        }

        return back()->with('success', __('comunidade.flash.post_created'));
    }

    public function update(Request $request, ComunidadePost $post): RedirectResponse
    {
        $this->ensureOwner($post->usuario_id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaIds = $user->materiasUnicas()->pluck('id');

        $data = $request->validate([
            'titulo' => 'required|string|max:180',
            'materia_id' => ['required', 'integer', Rule::in($materiaIds)],
            'conteudo' => 'required|string|max:2000',
        ]);

        $post->update($data);

        return back()->with('success', __('comunidade.flash.post_updated'));
    }

    public function destroy(ComunidadePost $post): RedirectResponse
    {
        $this->ensureOwner($post->usuario_id);

        foreach ($post->imagens as $imagem) {
            Storage::disk('public')->delete($imagem->caminho);
        }
        $post->delete();

        return back()->with('success', __('comunidade.flash.post_deleted'));
    }

    public function toggleCurtidaPost(Request $request, ComunidadePost $post): RedirectResponse|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($post->curtidas()->where('users.id', $user->id)->exists()) {
            $post->curtidas()->detach($user->id);
            $curtido = false;
        } else {
            $post->curtidas()->attach($user->id);
            $curtido = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['curtido' => $curtido, 'count' => $post->curtidas()->count()]);
        }

        return back();
    }

    public function comentar(Request $request, ComunidadePost $post): RedirectResponse
    {
        $data = $request->validate([
            'conteudo' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $parentId = null;
        if (!empty($data['parent_id'])) {
            $parent = ComunidadeComentario::where('post_id', $post->id)->find($data['parent_id']);
            if ($parent) {
                // Achata resposta-de-resposta em um único nível: sempre aponta pro comentário raiz.
                $parentId = $parent->parent_id ?? $parent->id;
            }
        }

        ComunidadeComentario::create([
            'post_id' => $post->id,
            'parent_id' => $parentId,
            'usuario_id' => $user->id,
            'conteudo' => $data['conteudo'],
        ]);

        return back()->with('success', __('comunidade.flash.comment_created'));
    }

    public function updateComentario(Request $request, ComunidadePost $post, ComunidadeComentario $comentario): RedirectResponse
    {
        if ((int) $comentario->post_id !== (int) $post->id) {
            abort(404);
        }
        $this->ensureOwner($comentario->usuario_id);

        $data = $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        $comentario->update($data);

        return back()->with('success', __('comunidade.flash.comment_updated'));
    }

    public function destroyComentario(ComunidadePost $post, ComunidadeComentario $comentario): RedirectResponse
    {
        if ((int) $comentario->post_id !== (int) $post->id) {
            abort(404);
        }
        $this->ensureOwner($comentario->usuario_id);
        $comentario->delete();

        return back()->with('success', __('comunidade.flash.comment_deleted'));
    }

    public function toggleCurtidaComentario(Request $request, ComunidadePost $post, ComunidadeComentario $comentario): RedirectResponse|JsonResponse
    {
        if ((int) $comentario->post_id !== (int) $post->id) {
            abort(404);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($comentario->curtidas()->where('users.id', $user->id)->exists()) {
            $comentario->curtidas()->detach($user->id);
            $curtido = false;
        } else {
            $comentario->curtidas()->attach($user->id);
            $curtido = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['curtido' => $curtido, 'count' => $comentario->curtidas()->count()]);
        }

        return back();
    }

    public function denunciarPost(Request $request, ComunidadePost $post): RedirectResponse
    {
        $this->registrarDenuncia('post', (int) $post->id, $request);

        return back()->with('success', __('comunidade.flash.reported'));
    }

    public function denunciarComentario(Request $request, ComunidadePost $post, ComunidadeComentario $comentario): RedirectResponse
    {
        if ((int) $comentario->post_id !== (int) $post->id) {
            abort(404);
        }
        $this->registrarDenuncia('comentario', (int) $comentario->id, $request);

        return back()->with('success', __('comunidade.flash.reported'));
    }

    private function registrarDenuncia(string $tipo, int $id, Request $request): void
    {
        $data = $request->validate([
            'motivo' => 'nullable|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        ComunidadeDenuncia::create([
            'usuario_id' => $user->id,
            'denunciavel_tipo' => $tipo,
            'denunciavel_id' => $id,
            'motivo' => $data['motivo'] ?? null,
        ]);
    }

    private function ensureOwner(int $usuarioId): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ((int) $usuarioId !== (int) $user->id) {
            abort(403);
        }
    }
}
