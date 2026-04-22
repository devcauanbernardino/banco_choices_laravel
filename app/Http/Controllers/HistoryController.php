<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
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
            $statusKey = $pct >= 0.7 ? 'aprovado' : 'reprovado';

            if ($filtroStatus !== '' && strtolower($filtroStatus) !== $statusKey) {
                continue;
            }

            $historico[] = [
                'id' => $row->id,
                'data' => date('d/m/Y H:i', strtotime($row->data_realizacao)),
                'materia' => ucfirst($row->materia),
                'pontuacao' => "{$acertos}/{$total}",
                'porcentagem' => round($pct * 100) . '%',
                'status' => $pct >= 0.7 ? __('dashboard.status.approved') : __('dashboard.status.failed'),
                'classe' => $pct >= 0.7 ? 'success' : 'danger',
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

        return view('history.index', compact(
            'historico', 'materias', 'totalSimulados', 'filtroMateria', 'filtroStatus', 'filtroQ', 'mediaPct'
        ));
    }
}
