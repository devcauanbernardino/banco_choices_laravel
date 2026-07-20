<?php

namespace App\Http\Controllers;

use App\Support\SimulationGrading;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $uid = Auth::id();
        $filtroMateria = $request->query('materia', '');
        $filtroStatus = $request->query('status', '');
        $filtroQ = trim((string) $request->query('q', ''));

        $query = DB::table('historico_simulados as h')
            ->join('materias as m', 'm.id', '=', 'h.materia_id')
            ->where('h.usuario_id', $uid)
            ->select('h.id', 'm.nome as materia', 'h.acertos', 'h.total_questoes', 'h.data_realizacao', 'h.detalhes_json');

        if ($filtroMateria !== '') {
            $query->where('m.nome', $filtroMateria);
        }

        if ($filtroQ !== '') {
            $like = '%'.$filtroQ.'%';
            $query->where('m.nome', 'like', $like);
        }

        $resultados = $query->orderByDesc('h.data_realizacao')->get();

        $historico = [];
        foreach ($resultados as $row) {
            $total = (int) $row->total_questoes;
            $acertos = (int) $row->acertos;
            $pct = $total > 0 ? $acertos / $total : 0;
            $statusKey = SimulationGrading::aprovado($pct * 100) ? 'aprovado' : 'reprovado';

            if ($filtroStatus !== '' && strtolower($filtroStatus) !== $statusKey) {
                continue;
            }

            $historico[] = [
                'id' => $row->id,
                'data' => date('d/m/Y H:i', strtotime($row->data_realizacao)),
                'materia' => ucfirst($row->materia),
                'pontuacao' => "{$acertos}/{$total}",
                'porcentagem' => round($pct * 100) . '%',
                'status' => SimulationGrading::aprovado($pct * 100) ? __('dashboard.status.approved') : __('dashboard.status.failed'),
                'classe' => SimulationGrading::aprovado($pct * 100) ? 'success' : 'danger',
            ];
        }

        $materias = DB::table('materias as m')
            ->join('usuarios_materias as um', 'um.materia_id', '=', 'm.id')
            ->where('um.usuario_id', $uid)
            ->select('m.nome')
            ->distinct()
            ->orderBy('m.nome')
            ->pluck('m.nome');

        $totalSimulados = count($historico);
        $mediaPct = 0.0;
        if ($totalSimulados > 0) {
            $sum = 0;
            foreach ($historico as $h) {
                $sum += (int) preg_replace('/\D+/', '', (string) ($h['porcentagem'] ?? '0'));
            }
            $mediaPct = round($sum / $totalSimulados, 1);
        }

        // Paginação em memória: o filtro de status é calculado em PHP (não é coluna do
        // banco), então o recorte por página só pode acontecer depois de montar $historico
        // por completo (totalSimulados/mediaPct também precisam do conjunto filtrado inteiro).
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $historico = new LengthAwarePaginator(
            array_slice($historico, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            $totalSimulados,
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('history.index', compact(
            'historico', 'materias', 'totalSimulados', 'filtroMateria', 'filtroStatus', 'filtroQ', 'mediaPct'
        ));
    }
}
