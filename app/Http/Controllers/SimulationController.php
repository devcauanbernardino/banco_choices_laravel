<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Support\Question;
use App\Support\SimulationSession;
use App\Support\SimulationTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    private SimulationSession $sim;

    public function __construct()
    {
        $this->sim = new SimulationSession;
    }

    public function create(Request $request)
    {
        $request->validate([
            'materia' => 'required|integer|exists:materias,id',
            'quantidade' => 'required|integer|min:1|max:200',
            'modo' => 'required|in:estudo,exame',
        ]);

        $user = Auth::user();
        $materiaId = (int) $request->input('materia');

        if (! $user->possuiMateria($materiaId)) {
            return redirect()->route('questionbank')->with('error', 'Matéria não autorizada.');
        }

        $materia = Materia::findOrFail($materiaId);
        $questoes = $this->loadQuestions($materiaId, (int) $request->input('quantidade'));

        if (empty($questoes)) {
            return redirect()->route('questionbank')->with('error', 'Nenhuma questão encontrada.');
        }

        $data = [
            'materia' => $materiaId,
            'materia_nome' => $materia->nome,
            'modo' => $request->input('modo'),
            'questoes' => $questoes,
            'atual' => 0,
            'respostas' => [],
            'feedback' => [],
        ];

        if ($request->input('modo') === 'exame') {
            $data['inicio'] = time();
            $data['tempo_total'] = SimulationTimer::DEFAULT_SECONDS;
        }

        $this->sim->init($data);

        return redirect()->route('simulation.show');
    }

    public function show()
    {
        if (! $this->sim->isActive()) {
            return redirect()->route('dashboard');
        }

        $this->ensureAuthorized();

        $questoes = (array) ($this->sim->get('questoes') ?? []);
        $indiceAtual = (int) ($this->sim->get('atual') ?? 0);

        if (! isset($questoes[$indiceAtual])) {
            $indiceAtual = 0;
            $this->sim->set('atual', 0);
        }

        $questao = new Question($questoes[$indiceAtual]);

        $tempoRestante = null;
        if ($this->sim->get('modo') === 'exame' && $this->sim->get('inicio') !== null) {
            $total = (int) ($this->sim->get('tempo_total') ?? SimulationTimer::DEFAULT_SECONDS);
            $tempoRestante = SimulationTimer::remainingSeconds((int) $this->sim->get('inicio'), $total);
            if (SimulationTimer::isExpired((int) $this->sim->get('inicio'), $total)) {
                return redirect()->route('result.show');
            }
        }

        $viewData = [
            'questao' => $questao,
            'indiceAtual' => $indiceAtual,
            'totalQuestoes' => count($questoes),
            'respostas' => $this->sim->get('respostas') ?? [],
            'modo' => $this->sim->get('modo') ?? 'estudo',
            'feedback' => ($this->sim->get('feedback') ?? [])[$indiceAtual] ?? null,
            'tempoRestante' => $tempoRestante,
            'materiaNome' => $this->sim->get('materia_nome') ?? '',
        ];

        return view('simulation.show', $viewData);
    }

    public function process(Request $request)
    {
        if (! $this->sim->isActive()) {
            return redirect()->route('home');
        }

        $this->ensureAuthorized();

        if ($request->has('timeout')) {
            return redirect()->route('result.show');
        }

        if ($request->has('resposta')) {
            $this->saveAnswer($request->input('resposta'));
        }

        if ($request->has('ir')) {
            $this->sim->set('atual', (int) $request->input('ir'));

            return redirect()->route('simulation.show');
        }

        if ($request->has('avancar')) {
            $currentIndex = (int) $this->sim->get('atual');
            $totalQuestoes = count((array) ($this->sim->get('questoes') ?? []));
            $next = $currentIndex + 1;

            if ($next >= $totalQuestoes) {
                return redirect()->route('result.show');
            }

            $this->sim->set('atual', $next);

            return redirect()->route('simulation.show');
        }

        if ($request->has('voltar')) {
            $current = (int) $this->sim->get('atual');
            $this->sim->set('atual', max(0, $current - 1));

            return redirect()->route('simulation.show');
        }

        return redirect()->route('simulation.show');
    }

    private function saveAnswer(string|int $userAnswer): void
    {
        $currentIndex = (int) $this->sim->get('atual');
        $questoes = (array) ($this->sim->get('questoes') ?? []);
        $modo = (string) ($this->sim->get('modo') ?? 'estudo');
        $answerStr = is_int($userAnswer) ? (string) $userAnswer : $userAnswer;

        $respostas = (array) ($this->sim->get('respostas') ?? []);
        $respostas[$currentIndex] = $answerStr;
        $this->sim->set('respostas', $respostas);

        if ($modo === 'estudo' && isset($questoes[$currentIndex])) {
            $questao = new Question($questoes[$currentIndex]);

            $feedbacks = (array) ($this->sim->get('feedback') ?? []);
            $feedbacks[$currentIndex] = [
                'acertou' => $questao->isCorrect($answerStr),
                'resposta_usuario' => $answerStr,
                'resposta_correta' => $questao->getCorrectAnswer(),
                'feedback' => $questao->getFeedback(),
            ];
            $this->sim->set('feedback', $feedbacks);
        }
    }

    private function loadQuestions(int $materiaId, int $quantidade): array
    {
        $map = [
            1 => 'questoes_microbiologia_refinado.json',
            2 => 'questoes_biologia_final_v2.json',
        ];

        $filename = $map[$materiaId] ?? null;
        if (! $filename) {
            $filename = "questoes_materia_{$materiaId}.json";
        }

        $path = storage_path('app/data/'.$filename);
        if (! file_exists($path)) {
            return [];
        }

        $data = json_decode(file_get_contents($path), true);
        if (! is_array($data)) {
            return [];
        }

        // Formato oficial (PHP legado): { "titulo": "...", "questoes": [ { "pergunta", "opcoes": [...] } ] }
        if (isset($data['questoes']) && is_array($data['questoes'])) {
            $lista = $data['questoes'];
        } elseif (array_is_list($data)) {
            $lista = $data;
        } else {
            return [];
        }

        if ($lista === []) {
            return [];
        }

        shuffle($lista);
        $quantidade = max(1, min($quantidade, count($lista)));

        return array_slice($lista, 0, $quantidade);
    }

    private function ensureAuthorized(): void
    {
        $user = Auth::user();
        $materiaId = $this->sim->get('materia');

        if ($materiaId && is_numeric($materiaId) && ! $user->possuiMateria((int) $materiaId)) {
            $this->sim->clear();
            abort(403);
        }
    }
}
