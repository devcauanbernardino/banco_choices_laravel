<?php

namespace App\Console\Commands;

use App\Models\Materia;
use App\Models\Questao;
use App\Services\Questions\QuestoesMetadataSync;
use App\Support\Question;
use App\Support\QuestionBankLocator;
use Illuminate\Console\Command;

class MarcarDemoCommand extends Command
{
    protected $signature = 'banco:marcar-demo {--por-materia=12}';

    protected $description = 'Marca N questões aleatórias como demo por matéria (2+ cátedras: N por cátedra; 2+ parciais: N por parcial)';

    public function handle(): int
    {
        $n = max(1, (int) $this->option('por-materia'));

        foreach (Materia::query()->orderBy('id')->cursor() as $materia) {
            QuestoesMetadataSync::syncMateria((int) $materia->id);
            $cats = $materia->catedras()->orderBy('ordem')->orderBy('id')->get();
            if ($cats->count() >= 2) {
                QuestoesMetadataSync::assignCatedrasEvenSplit((int) $materia->id);
            }

            $totalQuestoes = Questao::query()->where('materia_id', $materia->id)->count();
            if ($totalQuestoes === 0) {
                $this->line("Skip matéria {$materia->id} {$materia->nome}: sem questões — pulada.");

                continue;
            }

            $lista = QuestionBankLocator::loadCanonicalList((int) $materia->id);

            if ($cats->count() >= 2) {
                foreach ($cats as $cat) {
                    $marked = self::markAdditionalDemos($materia, $lista, $n, $cat->id, null);
                    if ($marked > 0) {
                        $this->info('Matéria '.$materia->id.' · '.$cat->nome.' — marcadas '.$marked.'.');

                        continue;
                    }
                    $atualCat = Questao::query()
                        ->where('materia_id', $materia->id)
                        ->where('catedra_id', $cat->id)
                        ->where('is_demo', true)
                        ->count();
                    if ($atualCat >= $n) {
                        $this->line("Skip matéria {$materia->id} · {$cat->nome}: já há {$atualCat} demo.");

                        continue;
                    }
                    $this->line("Skip matéria {$materia->id} · {$cat->nome}: sem questões válidas para demo.");
                }

                continue;
            }

            $parciais = Questao::query()
                ->where('materia_id', $materia->id)
                ->whereNotNull('parcial')
                ->where('parcial', '!=', '')
                ->distinct()
                ->orderBy('parcial')
                ->pluck('parcial')
                ->map(fn ($p) => (string) $p)
                ->all();

            if (count($parciais) >= 2) {
                $markedTotal = 0;
                foreach ($parciais as $parcial) {
                    $marked = self::markAdditionalDemos($materia, $lista, $n, null, $parcial);
                    $markedTotal += $marked;
                    if ($marked > 0) {
                        $this->info('Matéria '.$materia->id.' · parcial '.$parcial.' — marcadas '.$marked.'.');
                    }
                }
                if ($markedTotal > 0) {
                    continue;
                }
                $atual = Questao::query()->where('materia_id', $materia->id)->where('is_demo', true)->count();
                if ($atual >= $n * count($parciais)) {
                    $this->line("Skip matéria {$materia->id} {$materia->nome}: demo por parcial já preenchido.");

                    continue;
                }
                $this->line("Skip matéria {$materia->id} {$materia->nome}: sem questões válidas para demo.");

                continue;
            }

            $marked = self::markAdditionalDemos($materia, $lista, $n, null, null);
            if ($marked > 0) {
                $this->info('Matéria '.$materia->id.' — marcadas '.$marked.' questões.');

                continue;
            }
            $atual = Questao::query()->where('materia_id', $materia->id)->where('is_demo', true)->count();
            if ($atual >= $n) {
                $this->line("Skip matéria {$materia->id} {$materia->nome}: já há {$atual} demo.");

                continue;
            }
            $this->line("Skip matéria {$materia->id} {$materia->nome}: sem questões válidas para demo.");
        }

        return self::SUCCESS;
    }

    /**
     * @return int Número de linhas recém marcadas como demo
     */
    private static function markAdditionalDemos(Materia $materia, array $lista, int $n, ?int $catedraId, ?string $parcial): int
    {
        $atualQ = Questao::query()
            ->where('materia_id', $materia->id)
            ->where('is_demo', true);
        if ($catedraId !== null) {
            $atualQ->where('catedra_id', $catedraId);
        }
        if ($parcial !== null && $parcial !== '') {
            $atualQ->where('parcial', $parcial);
        }
        $atual = (int) $atualQ->count();
        if ($atual >= $n) {
            return 0;
        }

        $needed = $n - $atual;

        $candQ = Questao::query()
            ->where('materia_id', $materia->id)
            ->where('is_demo', false);
        if ($catedraId !== null) {
            $candQ->where('catedra_id', $catedraId);
        }
        if ($parcial !== null && $parcial !== '') {
            $candQ->where('parcial', $parcial);
        }

        $candidates = $candQ->inRandomOrder()->get(['id', 'overlay_key']);

        $ids = [];
        foreach ($candidates as $cand) {
            if (count($ids) >= $needed) {
                break;
            }
            $blob = $lista[(int) $cand->overlay_key] ?? null;
            if (! is_array($blob) || ! self::looksUsable($blob)) {
                continue;
            }
            $ids[] = $cand->id;
        }

        if ($ids === []) {
            return 0;
        }

        Questao::query()->whereIn('id', $ids)->update(['is_demo' => true]);

        return count($ids);
    }

    /**
     * Aceita apenas questões com enunciado e alternativas com texto real
     * (descarta placeholders tipo "A","B","C","D" ou strings vazias/curtas demais).
     */
    private static function looksUsable(array $blob): bool
    {
        $q = new Question($blob);

        // O demo público (UI de radio button) ainda não suporta questões "assinale todas as corretas".
        if ($q->isMultiResposta()) {
            return false;
        }

        $perg = trim($q->getPergunta());
        if ($perg === '' || $perg === 'Questão sem título') {
            return false;
        }

        $opts = $q->getOpcoes();
        if (count($opts) < 2) {
            return false;
        }

        foreach ($opts as $i => $txt) {
            $t = trim((string) $txt);
            $letra = chr(ord('A') + $i);
            if (mb_strlen($t) < 4) {
                return false;
            }
            if (strcasecmp($t, $letra) === 0) {
                return false;
            }
        }

        return true;
    }
}
