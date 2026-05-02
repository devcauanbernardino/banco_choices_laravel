<?php

namespace App\Services\Questions;

use App\Models\Catedra;
use App\Models\Materia;
use App\Models\Questao;
use App\Support\QuestionBankLocator;

class QuestoesMetadataSync
{
    /** Cria linhas em questoes para cada entrada do JSON quando faltarem. */
    public static function syncMateria(int $materiaId): int
    {
        $lista = QuestionBankLocator::loadCanonicalList($materiaId);
        if ($lista === []) {
            return 0;
        }

        foreach (array_keys($lista) as $k) {
            $overlayKey = (int) $k;
            $existing = Questao::query()->where('materia_id', $materiaId)
                ->where('overlay_key', $overlayKey)
                ->exists();
            if ($existing) {
                continue;
            }
            Questao::query()->create([
                'materia_id' => $materiaId,
                'catedra_id' => $hasCathedrals ? null : null,
                'overlay_key' => $overlayKey,
                'parcial' => null,
                'tema' => null,
                'is_demo' => false,
            ]);
            $inserted++;
        }

        return $inserted;
    }

    public static function assignCatedrasEvenSplit(int $materiaId): void
    {
        $cats = Catedra::query()->where('materia_id', $materiaId)->orderBy('ordem')->orderBy('id')->get();
        if ($cats->count() < 2) {
            return;
        }

        $n = Questao::query()->where('materia_id', $materiaId)->count();
        if ($n === 0) {
            return;
        }

        /** @var Catedra $a */
        /** @var Catedra $b */
        [$a, $b] = [$cats[0], $cats[1]];
        foreach (Questao::query()->where('materia_id', $materiaId)->orderBy('overlay_key')->get() as $i => $q) {
            $q->update(['catedra_id' => ($i % 2 === 0) ? $a->id : $b->id]);
        }
    }
}
