<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        $uid = Auth::id();

        $kpiRow = DB::table('historico_simulados')
            ->where('usuario_id', $uid)
            ->selectRaw('SUM(total_questoes) as total, SUM(acertos) as acertos, COUNT(id) as simulados')
            ->first();

        $totalResp = (int) ($kpiRow?->total ?? 0);
        $totalAcertos = (int) ($kpiRow?->acertos ?? 0);
        $totalSimulados = (int) ($kpiRow?->simulados ?? 0);
        $mediaAcertos = $totalResp > 0 ? round(($totalAcertos / $totalResp) * 100, 1) : 0;

        $porMateria = DB::table('historico_simulados as h')
            ->join('materias as m', 'm.id', '=', 'h.materia_id')
            ->where('h.usuario_id', $uid)
            ->selectRaw('m.nome, SUM(h.acertos) as acertos, SUM(h.total_questoes) as total')
            ->groupBy('h.materia_id', 'm.nome')
            ->orderByRaw('(SUM(h.acertos)/SUM(h.total_questoes)) DESC')
            ->get()
            ->map(fn ($r) => [
                'nome' => $r->nome,
                'porcentagem' => (int) $r->total > 0 ? round(($r->acertos / $r->total) * 100) : 0,
            ])->toArray();

        $melhorMateria = ! empty($porMateria) ? $porMateria[0]['nome'] : 'N/A';

        $evolucaoRows = DB::table('historico_simulados')
            ->where('usuario_id', $uid)
            ->selectRaw('DATE(data_realizacao) as data, (SUM(acertos)/SUM(total_questoes))*100 as desempenho')
            ->groupByRaw('DATE(data_realizacao)')
            ->orderBy('data')
            ->limit(10)
            ->get();

        $evolucao = [
            'labels' => $evolucaoRows->map(fn ($r) => Carbon::parse($r->data)->format('d/m'))->values()->all(),
            'data' => $evolucaoRows->map(fn ($r) => round((float) ($r->desempenho ?? 0), 1))->values()->all(),
        ];

        $semanal = DB::table('historico_simulados')
            ->where('usuario_id', $uid)
            ->selectRaw('YEARWEEK(data_realizacao, 1) as semana, MIN(DATE(data_realizacao)) as inicio_semana, SUM(total_questoes) as total, SUM(acertos) as acertos')
            ->groupByRaw('YEARWEEK(data_realizacao, 1)')
            ->orderByDesc('semana')
            ->limit(4)
            ->get()
            ->map(fn ($r) => [
                'inicio_semana' => $r->inicio_semana,
                'total' => (int) ($r->total ?? 0),
                'acertos' => (int) ($r->acertos ?? 0),
            ])->all();

        return view('stats.index', compact(
            'totalResp',
            'totalAcertos',
            'totalSimulados',
            'mediaAcertos',
            'melhorMateria',
            'porMateria',
            'evolucao',
            'semanal'
        ));
    }
}
