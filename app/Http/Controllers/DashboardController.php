<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $uid = Auth::id();

        $stats = $this->getStats($uid);
        $recentes = $this->getRecentSimulados($uid);
        $evolucao = $this->getEvolucaoGrafico($uid);
        $porMateria = $this->getDesempenhoPorMateria($uid);

        $usuario = Auth::user();

        return view('dashboard.index', compact(
            'stats', 'recentes', 'evolucao', 'porMateria', 'usuario'
        ));
    }

    private function calcularSequenciaReal(int $uid): int
    {
        $datas = DB::table('historico_simulados')
            ->where('usuario_id', $uid)
            ->selectRaw('DISTINCT DATE(data_realizacao) as data')
            ->orderByDesc('data')
            ->pluck('data')
            ->toArray();

        if (empty($datas)) return 0;

        $sequencia = 0;
        $hoje = new \DateTime('today');
        $ultimaData = new \DateTime($datas[0]);

        if ($hoje->diff($ultimaData)->days > 1) return 0;

        $dataComparacao = $ultimaData;
        foreach ($datas as $i => $dataStr) {
            $dataAtual = new \DateTime($dataStr);
            if ($i === 0) {
                $sequencia = 1;
                continue;
            }
            if ($dataComparacao->diff($dataAtual)->days === 1) {
                $sequencia++;
                $dataComparacao = $dataAtual;
            } else {
                break;
            }
        }

        return $sequencia;
    }

    private function getStats(int $uid): array
    {
        $dados = DB::table('historico_simulados')
            ->where('usuario_id', $uid)
            ->selectRaw('SUM(total_questoes) as total_respondidas, SUM(acertos) as total_acertos, COUNT(id) as total_simulados')
            ->first();

        $totalResp = (int) ($dados->total_respondidas ?? 0);
        $totalAcertos = (int) ($dados->total_acertos ?? 0);
        $aproveitamento = $totalResp > 0 ? round(($totalAcertos / $totalResp) * 100, 1) : 0;

        $porMateria = $this->getDesempenhoPorMateria($uid);
        $melhorNome = !empty($porMateria) ? $porMateria[0]['nome'] : 'N/A';

        return [
            'questoes_respondidas' => $totalResp,
            'aproveitamento_geral' => $aproveitamento,
            'total_simulados' => (int) ($dados->total_simulados ?? 0),
            'pontuacao_total' => $totalAcertos * 10,
            'melhor_materia' => $melhorNome,
            'sequencia_dias' => $this->calcularSequenciaReal($uid),
        ];
    }

    private function getRecentSimulados(int $uid): array
    {
        $rows = DB::table('historico_simulados as h')
            ->join('materias as m', 'm.id', '=', 'h.materia_id')
            ->where('h.usuario_id', $uid)
            ->orderByDesc('h.data_realizacao')
            ->limit(5)
            ->select('m.nome as materia', 'h.acertos', 'h.total_questoes', 'h.data_realizacao')
            ->get();

        return $rows->map(function ($row) {
            $total = (int) $row->total_questoes;
            $acertos = (int) $row->acertos;
            $pct = $total > 0 ? $acertos / $total : 0;

            return [
                'data' => date('d/m/Y', strtotime($row->data_realizacao)),
                'categoria' => $row->materia,
                'pontuacao' => "{$acertos}/{$total}",
                'status' => $pct >= 0.7 ? __('dashboard.status.approved') : __('dashboard.status.failed'),
                'classe' => $pct >= 0.7 ? 'success' : 'danger',
            ];
        })->toArray();
    }

    private function getEvolucaoGrafico(int $uid): array
    {
        $rows = DB::table('historico_simulados')
            ->where('usuario_id', $uid)
            ->selectRaw('DATE(data_realizacao) as data, (SUM(acertos)/SUM(total_questoes))*100 as desempenho')
            ->groupByRaw('DATE(data_realizacao)')
            ->orderBy('data')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];
        foreach ($rows as $row) {
            $labels[] = date('d/m', strtotime($row->data));
            $data[] = round($row->desempenho, 1);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getDesempenhoPorMateria(int $uid): array
    {
        $rows = DB::table('historico_simulados as h')
            ->join('materias as m', 'm.id', '=', 'h.materia_id')
            ->where('h.usuario_id', $uid)
            ->selectRaw('m.nome as materia, SUM(h.acertos) as acertos, SUM(h.total_questoes) as total')
            ->groupBy('h.materia_id', 'm.nome')
            ->orderByRaw('(SUM(h.acertos)/SUM(h.total_questoes)) DESC')
            ->get();

        return $rows->map(function ($row) {
            $total = (int) $row->total;
            return [
                'nome' => $row->materia,
                'porcentagem' => $total > 0 ? round(($row->acertos / $total) * 100) : 0,
            ];
        })->toArray();
    }
}
