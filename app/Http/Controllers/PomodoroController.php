<?php

namespace App\Http\Controllers;

use App\Models\PomodoroCiclo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PomodoroController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materias = $user->materiasUnicas();

        $hoje = $this->statsHoje((int) $user->id);
        $streak = $this->calcularStreakDias((int) $user->id);
        $porMateria = $this->totalPorMateria((int) $user->id);
        $sessoesRecentes = $this->sessoesRecentes((int) $user->id);

        return view('pomodoro.index', compact('materias', 'hoje', 'streak', 'porMateria', 'sessoesRecentes'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'materia_id' => 'required|integer|exists:materias,id',
            'sessao_uid' => 'required|string|max:40',
            'duracao_minutos' => 'required|integer|min:1|max:180',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $materiaId = (int) $request->input('materia_id');

        if (! $user->possuiMateria($materiaId)) {
            return response()->json(['error' => __('pomodoro.err.unauthorized_subject')], 403);
        }

        PomodoroCiclo::create([
            'usuario_id' => $user->id,
            'materia_id' => $materiaId,
            'sessao_uid' => (string) $request->input('sessao_uid'),
            'duracao_minutos' => (int) $request->input('duracao_minutos'),
            'concluido_em' => now(),
        ]);

        return response()->json([
            'hoje' => $this->statsHoje((int) $user->id),
            'streak' => $this->calcularStreakDias((int) $user->id),
        ]);
    }

    /**
     * @return array{minutos: int, ciclos: int}
     */
    private function statsHoje(int $userId): array
    {
        $row = PomodoroCiclo::query()
            ->where('usuario_id', $userId)
            ->whereDate('concluido_em', now()->toDateString())
            ->selectRaw('COALESCE(SUM(duracao_minutos),0) as minutos, COUNT(id) as ciclos')
            ->first();

        return [
            'minutos' => (int) ($row->minutos ?? 0),
            'ciclos' => (int) ($row->ciclos ?? 0),
        ];
    }

    private function calcularStreakDias(int $userId): int
    {
        $datas = PomodoroCiclo::query()
            ->where('usuario_id', $userId)
            ->selectRaw('DISTINCT DATE(concluido_em) as data')
            ->orderByDesc('data')
            ->pluck('data')
            ->toArray();

        if (empty($datas)) {
            return 0;
        }

        $hoje = new \DateTime('today');
        $ultimaData = new \DateTime($datas[0]);

        if ($hoje->diff($ultimaData)->days > 1) {
            return 0;
        }

        $sequencia = 1;
        $dataComparacao = $ultimaData;
        foreach (array_slice($datas, 1) as $dataStr) {
            $dataAtual = new \DateTime($dataStr);
            if ($dataComparacao->diff($dataAtual)->days === 1) {
                $sequencia++;
                $dataComparacao = $dataAtual;
            } else {
                break;
            }
        }

        return $sequencia;
    }

    /**
     * @return list<array{materia_nome: string, total_minutos: int, total_ciclos: int}>
     */
    private function totalPorMateria(int $userId): array
    {
        return DB::table('pomodoro_ciclos')
            ->join('materias', 'materias.id', '=', 'pomodoro_ciclos.materia_id')
            ->where('pomodoro_ciclos.usuario_id', $userId)
            ->selectRaw('materias.nome as materia_nome, SUM(pomodoro_ciclos.duracao_minutos) as total_minutos, COUNT(pomodoro_ciclos.id) as total_ciclos')
            ->groupBy('materias.id', 'materias.nome')
            ->orderByDesc('total_minutos')
            ->get()
            ->map(fn ($r) => [
                'materia_nome' => $r->materia_nome,
                'total_minutos' => (int) $r->total_minutos,
                'total_ciclos' => (int) $r->total_ciclos,
            ])
            ->all();
    }

    /**
     * @return list<array{materia_nome: string, total_ciclos: int, total_minutos: int, data: string}>
     */
    private function sessoesRecentes(int $userId): array
    {
        return DB::table('pomodoro_ciclos')
            ->join('materias', 'materias.id', '=', 'pomodoro_ciclos.materia_id')
            ->where('pomodoro_ciclos.usuario_id', $userId)
            ->selectRaw('pomodoro_ciclos.sessao_uid, materias.nome as materia_nome, COUNT(pomodoro_ciclos.id) as total_ciclos, SUM(pomodoro_ciclos.duracao_minutos) as total_minutos, MAX(pomodoro_ciclos.concluido_em) as ultima_em')
            ->groupBy('pomodoro_ciclos.sessao_uid', 'materias.nome')
            ->orderByDesc('ultima_em')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'materia_nome' => $r->materia_nome,
                'total_ciclos' => (int) $r->total_ciclos,
                'total_minutos' => (int) $r->total_minutos,
                'data' => $r->ultima_em,
            ])
            ->all();
    }
}
