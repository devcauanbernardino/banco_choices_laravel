<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculdade;
use App\Models\Materia;
use App\Services\Questions\QuestionExamBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Endpoints AJAX do catálogo (auth obrigatório via rota web). */
class CatalogoAjaxController extends Controller
{
    public function faculdades(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $owned = collect($user->materias()->pluck('materias.id')->all())->unique()->filter(fn ($id) => (int) $id > 0)->values();

        $q = Faculdade::query()->where('ativo', true)->orderBy('ordem');
        $q->whereHas('agrupamentos.materias', function ($wq) use ($owned) {
            $wq->whereIn('materias.id', $owned);
        });

        $rows = $q->get(['id', 'nome', 'slug', 'ordem']);

        return response()->json(['data' => $rows]);
    }

    public function agrupamentos(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $owned = collect($user->materias()->pluck('materias.id')->all())->unique()->values();

        $fid = (int) $request->query('faculdade_id');
        if ($fid <= 0) {
            return response()->json(['data' => []], 422);
        }

        $rows = Faculdade::query()
            ->whereKey($fid)
            ->whereHas('agrupamentos.materias', fn ($wq) => $wq->whereIn('materias.id', $owned))
            ->with(['agrupamentos' => fn ($r) => $r->orderBy('ordem')
                ->whereHas('materias', fn ($mq) => $mq->whereIn('materias.id', $owned))
                ->with(['materias' => fn ($mq) => $mq->orderBy('ordem')->whereIn('materias.id', $owned)]),
            ])
            ->first();

        return response()->json(['data' => $rows ? $rows->agrupamentos : collect()]);
    }

    /** Lista apenas matérias que o utilizador já comprou neste agrupamento. */
    public function materias(Request $request): \Illuminate\Http\JsonResponse
    {
        $aid = (int) $request->query('agrupamento_id');
        if ($aid <= 0) {
            return response()->json(['data' => []], 422);
        }

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        if (! $user instanceof \App\Models\User) {
            return response()->json(['data' => []], 401);
        }

        $owned = $user->materias()->pluck('materias.id')->all();

        $q = Materia::query()
            ->where('agrupamento_id', $aid)
            ->whereIn('id', $owned)
            ->orderBy('ordem')
            ->withCount('catedras');

        $rows = $q->get()->map(fn (Materia $m) => [
            'id' => $m->id,
            'nome' => $m->nome,
            'slug' => $m->slug,
            'ordem' => $m->ordem,
            'catedras_count' => (int) ($m->catedras_count ?? 0),
        ]);

        return response()->json(['data' => $rows]);
    }

    public function catedras(Request $request): \Illuminate\Http\JsonResponse
    {
        $mid = (int) $request->query('materia_id');
        if ($mid <= 0) {
            return response()->json(['data' => []], 422);
        }

        /** @var Materia|null $m */
        $m = Materia::query()->with(['catedras' => fn ($q) => $q->orderBy('ordem')])->find($mid);

        return response()->json(['data' => $m ? $m->catedras : collect()]);
    }

    public function temas(Request $request): \Illuminate\Http\JsonResponse
    {
        $mid = (int) $request->query('materia_id');
        $cid = $request->query('catedra_id');
        $cid = ($cid !== null && $cid !== '') ? (int) $cid : null;

        if ($mid <= 0) {
            return response()->json(['data' => []], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->possuiMateria($mid)) {
            return response()->json(['data' => []], 403);
        }

        $temas = QuestionExamBuilder::temasDisponiveis($mid, $cid);

        return response()->json(['data' => $temas]);
    }

    public function parciais(Request $request): \Illuminate\Http\JsonResponse
    {
        $mid = (int) $request->query('materia_id');
        $cid = $request->query('catedra_id');
        $cid = ($cid !== null && $cid !== '') ? (int) $cid : null;
        if ($mid <= 0) {
            return response()->json(['data' => [], 'hay_final_pool' => false], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->possuiMateria($mid)) {
            return response()->json(['data' => [], 'hay_final_pool' => false], 403);
        }

        $parc = QuestionExamBuilder::parciaisDisponiveis($mid, $cid);
        $final = QuestionExamBuilder::hayFinalPool($mid, $cid);

        return response()->json([
            'data' => $parc,
            'hay_final_pool' => $final['hay'],
        ]);
    }
}
