<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculdade;
use App\Models\Materia;
use Illuminate\Http\Request;

/** Catálogo completo para fluxos públicos (signup / demo / addons via filtro próprio no blade). */
class CatalogoPublicController extends Controller
{
    public function faculdades(): \Illuminate\Http\JsonResponse
    {
        $rows = Faculdade::query()->where('ativo', true)->orderBy('ordem')->get(['id', 'nome', 'slug', 'ordem']);

        return response()->json(['data' => $rows]);
    }

    public function agrupamentos(Request $request): \Illuminate\Http\JsonResponse
    {
        $fid = (int) $request->query('faculdade_id');
        if ($fid <= 0) {
            return response()->json(['data' => []], 422);
        }

        $fac = Faculdade::query()
            ->whereKey($fid)
            ->with(['agrupamentos' => fn ($r) => $r->orderBy('ordem')->with(['materias' => fn ($mq) => $mq->orderBy('ordem')])])
            ->first();

        return response()->json(['data' => $fac ? $fac->agrupamentos : collect()]);
    }

    /**
     * @param  excludedIds  opcional lista CSV de materias já no carrinho (signup) ou já compradas (addon AJAX)
     */
    public function materias(Request $request): \Illuminate\Http\JsonResponse
    {
        $aid = (int) $request->query('agrupamento_id');
        if ($aid <= 0) {
            return response()->json(['data' => []], 422);
        }

        $exclude = [];
        $rawEx = trim((string) $request->query('exclude_ids', ''));
        if ($rawEx !== '') {
            foreach (explode(',', $rawEx) as $p) {
                $i = (int) trim($p);
                if ($i > 0) {
                    $exclude[] = $i;
                }
            }
        }

        $q = Materia::query()->where('agrupamento_id', $aid)->orderBy('ordem')->withCount('catedras');
        if ($exclude !== []) {
            $q->whereNotIn('id', array_unique($exclude));
        }

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
}
