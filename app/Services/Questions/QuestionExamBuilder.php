<?php

namespace App\Services\Questions;

use App\Models\Questao;
use App\Support\QuestionBankLocator;

class QuestionExamBuilder
{
    /**
     * @param  list<string>  $parciaisRaw
     * @param  list<string>  $temasRaw
     * @return list<array<string, mixed>>
     */
    public static function buildPack(
        int $materiaId,
        ?int $catedraId,
        array $parciaisRaw,
        array $temasRaw,
        int $quantidade,
        bool $demoOnly = false
    ): array {
        $lista = QuestionBankLocator::loadCanonicalList($materiaId);
        if ($lista === []) {
            return [];
        }

        foreach ($lista as $i => &$row) {
            if (is_array($row)) {
                $row['_overlay_key'] = $i;
            }
        }
        unset($row);

        $overlayMeta = Questao::query()
            ->where('materia_id', $materiaId)
            ->when($demoOnly, fn ($q) => $q->where('is_demo', true))
            ->get()
            ->keyBy('overlay_key');

        $parciais = self::normalizeList($parciaisRaw);
        $temas = self::normalizeList($temasRaw);
        $filterActive = $parciais !== [] || $temas !== [];

        $hasFinal = in_array('final', $parciais, true);

        $eligibleIdx = [];

        foreach ($lista as $i => $blob) {
            if (! is_array($blob)) {
                continue;
            }

            /** @var Questao|null $meta */
            $meta = $overlayMeta->get((int) $i);

            if ($catedraId !== null) {
                if ($meta === null) {
                    continue;
                }
                if ($meta->catedra_id !== null && (int) $meta->catedra_id !== (int) $catedraId) {
                    continue;
                }
            }

            if (! $filterActive) {
                $eligibleIdx[] = $i;

                continue;
            }

            if ($meta === null) {
                continue;
            }

            if ($parciais !== [] && ! $hasFinal) {
                $p = trim((string) ($meta->parcial ?? ''));
                if ($p === '' || ! in_array($p, $parciais, true)) {
                    continue;
                }
            }

            if ($temas !== []) {
                $t = trim((string) ($meta->tema ?? ''));
                if ($t === '' || ! in_array($t, $temas, true)) {
                    continue;
                }
            }

            $eligibleIdx[] = $i;
        }

        $pack = [];

        foreach ($eligibleIdx as $i) {
            $row = $lista[$i];
            if (! is_array($row)) {
                continue;
            }

            /** @var Questao|null $meta */
            $meta = $overlayMeta->get((int) $i);
            $row['_parcial'] = $meta ? $meta->parcial : null;
            $row['_tema'] = $meta ? $meta->tema : null;
            $pack[] = $row;
        }

        if ($filterActive && $pack === []) {
            return [];
        }

        shuffle($pack);
        $quantidade = max(1, min($quantidade, count($pack)));

        return array_slice($pack, 0, $quantidade);
    }

    /**
     * @return list<string>
     */
    public static function parciaisDisponiveis(int $materiaId, ?int $catedraId = null): array
    {
        $q = Questao::query()->where('materia_id', $materiaId)->whereNotNull('parcial');
        if ($catedraId !== null) {
            $q->where(function ($w) use ($catedraId) {
                $w->whereNull('catedra_id')->orWhere('catedra_id', $catedraId);
            });
        }

        return $q->distinct()->orderBy('parcial')->pluck('parcial')->map(fn ($p) => (string) $p)->all();
    }

    /** @return list<string> */
    public static function temasDisponiveis(int $materiaId, ?int $catedraId = null): array
    {
        $q = Questao::query()->where('materia_id', $materiaId)->whereNotNull('tema');
        if ($catedraId !== null) {
            $q->where(function ($w) use ($catedraId) {
                $w->whereNull('catedra_id')->orWhere('catedra_id', $catedraId);
            });
        }

        return $q->distinct()->orderBy('tema')->pluck('tema')->map(fn ($t) => (string) $t)->all();
    }

    /**
     * @return list<string>
     */
    private static function normalizeList(array $raw): array
    {
        $out = [];
        foreach ($raw as $v) {
            $s = is_string($v) ? trim($v) : trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<mixed>  $raw
     * @return list<string>
     */
    public static function normalizedFilterTokens(array $raw): array
    {
        return self::normalizeList($raw);
    }

    /** @return array{hay:bool,total:int} */
    public static function hayFinalPool(int $materiaId, ?int $catedraId = null): array
    {
        $q = Questao::query()->where('materia_id', $materiaId);
        if ($catedraId !== null) {
            $q->where(function ($w) use ($catedraId) {
                $w->whereNull('catedra_id')->orWhere('catedra_id', $catedraId);
            });
        }
        $total = (int) $q->clone()->count();

        return ['hay' => $total > 0, 'total' => $total];
    }
}
