<?php

namespace App\Http\Controllers;

use App\Models\ComunidadeComentario;
use App\Models\ComunidadeDenuncia;
use App\Models\ComunidadePost;
use App\Models\ComunidadePostImagem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ComunidadeController extends Controller
{
    public const MAX_IMAGENS_POR_POST = 6;

    public function index()
    {
        $posts = ComunidadePost::query()
            ->with(['usuario:id,nome', 'imagens', 'comentarios.usuario:id,nome'])
            ->withCount('comentarios')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('comunidade.index', compact('posts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'conteudo' => 'required|string|max:2000',
            'imagens' => 'nullable|array|max:'.self::MAX_IMAGENS_POR_POST,
            'imagens.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $post = ComunidadePost::create([
            'usuario_id' => $user->id,
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

        return redirect()->route('comunidade.index')->with('success', __('comunidade.flash.post_created'));
    }

    public function destroy(ComunidadePost $post): RedirectResponse
    {
        $this->ensureOwner($post->usuario_id);

        foreach ($post->imagens as $imagem) {
            Storage::disk('public')->delete($imagem->caminho);
        }
        $post->delete();

        return redirect()->route('comunidade.index')->with('success', __('comunidade.flash.post_deleted'));
    }

    public function comentar(Request $request, ComunidadePost $post): RedirectResponse
    {
        $data = $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        ComunidadeComentario::create([
            'post_id' => $post->id,
            'usuario_id' => $user->id,
            'conteudo' => $data['conteudo'],
        ]);

        return redirect()->route('comunidade.index')->with('success', __('comunidade.flash.comment_created'));
    }

    public function destroyComentario(ComunidadePost $post, ComunidadeComentario $comentario): RedirectResponse
    {
        if ((int) $comentario->post_id !== (int) $post->id) {
            abort(404);
        }
        $this->ensureOwner($comentario->usuario_id);
        $comentario->delete();

        return redirect()->route('comunidade.index')->with('success', __('comunidade.flash.comment_deleted'));
    }

    public function denunciarPost(Request $request, ComunidadePost $post): RedirectResponse
    {
        $this->registrarDenuncia('post', (int) $post->id, $request);

        return redirect()->route('comunidade.index')->with('success', __('comunidade.flash.reported'));
    }

    public function denunciarComentario(Request $request, ComunidadePost $post, ComunidadeComentario $comentario): RedirectResponse
    {
        if ((int) $comentario->post_id !== (int) $post->id) {
            abort(404);
        }
        $this->registrarDenuncia('comentario', (int) $comentario->id, $request);

        return redirect()->route('comunidade.index')->with('success', __('comunidade.flash.reported'));
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
