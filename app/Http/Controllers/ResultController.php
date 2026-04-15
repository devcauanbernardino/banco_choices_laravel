<?php

namespace App\Http\Controllers;

use App\Models\HistoricoSimulado;
use App\Support\Question;
use App\Support\SimulationSession;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function show()
    {
        $sim = new SimulationSession();

        if (!$sim->isActive()) {
            return redirect()->route('dashboard');
        }

        $questoes = (array) ($sim->get('questoes') ?? []);
        $respostas = (array) ($sim->get('respostas') ?? []);
        $modo = $sim->get('modo') ?? 'estudo';
        $materiaId = $sim->get('materia');
        $materiaNome = $sim->get('materia_nome') ?? '';

        $acertos = 0;
        $total = count($questoes);
        $detalhes = [];

        foreach ($questoes as $i => $qData) {
            $q = new Question($qData);
            $userAnswer = $respostas[$i] ?? null;
            $correct = $q->isCorrect($userAnswer);
            if ($correct) $acertos++;

            $detalhes[] = [
                'pergunta' => $q->getPergunta(),
                'resposta_usuario' => $userAnswer,
                'resposta_correta' => $q->getCorrectAnswer(),
                'acertou' => $correct,
                'feedback' => $q->getFeedback(),
            ];
        }

        $tempoSegundos = null;
        if ($modo === 'exame' && $sim->get('inicio')) {
            $tempoSegundos = time() - (int) $sim->get('inicio');
        }

        // Save to history
        HistoricoSimulado::create([
            'usuario_id' => Auth::id(),
            'materia_id' => $materiaId,
            'acertos' => $acertos,
            'total_questoes' => $total,
            'detalhes_json' => [
                'v' => 1,
                'detalhes' => $detalhes,
                'tempo_segundos' => $tempoSegundos,
            ],
        ]);

        $sim->clear();

        $porcentagem = $total > 0 ? round(($acertos / $total) * 100, 1) : 0;

        return view('simulation.result', compact(
            'acertos', 'total', 'porcentagem', 'detalhes', 'modo',
            'materiaNome', 'tempoSegundos'
        ));
    }
}
