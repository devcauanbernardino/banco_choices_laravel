<?php

namespace App\Services\Questions;

use App\Models\Questao;
use App\Support\QuestionBankLocator;

class QuestionExamBuilder
{
    /** Token enviado nos filtros quando o usuário restringe a questões sem coluna `tema` preenchida. */
    public const TEMA_FILTRO_SEM_ETIQUETA = '__bc_sem_tema__';

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
        bool $demoOnly = false,
        bool $shufflePack = true
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

            if ($demoOnly && $meta === null) {
                continue;
            }

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
                $sentinel = self::TEMA_FILTRO_SEM_ETIQUETA;
                $querSemEtiqueta = in_array($sentinel, $temas, true);
                $temasNomeados = array_values(array_filter(
                    $temas,
                    static fn ($x) => trim((string) $x) !== $sentinel
                ));

                $bateNomeado = $temasNomeados !== [] && $t !== '' && in_array($t, $temasNomeados, true);
                $bateSemEtiqueta = $querSemEtiqueta && $t === '';

                if (! $bateNomeado && ! $bateSemEtiqueta) {
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

        if ($shufflePack) {
            shuffle($pack);
        }
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
        return array_column(self::temasDisponiveisComParciais($materiaId, $catedraId), 'tema');
    }

    /**
     * @return list<array{tema: string, parciais: list<string>}>
     */
    public static function temasDisponiveisComParciais(int $materiaId, ?int $catedraId = null): array
    {
        $q = Questao::query()
            ->where('materia_id', $materiaId)
            ->whereNotNull('tema')
            ->where('tema', '!=', '')
            ->select('tema', 'parcial');

        if ($catedraId !== null) {
            $q->where(function ($w) use ($catedraId) {
                $w->whereNull('catedra_id')->orWhere('catedra_id', $catedraId);
            });
        }

        $byTema = [];
        foreach ($q->distinct()->orderBy('tema')->get() as $row) {
            $tema = (string) $row->tema;
            if (! isset($byTema[$tema])) {
                $byTema[$tema] = [];
            }
            if ($row->parcial !== null && $row->parcial !== '') {
                $p = (string) $row->parcial;
                if (! in_array($p, $byTema[$tema], true)) {
                    $byTema[$tema][] = $p;
                }
            }
        }

        $out = [];
        foreach ($byTema as $tema => $parciais) {
            $out[] = ['tema' => $tema, 'parciais' => array_values($parciais)];
        }

        if (self::hayQuestoesSemTemaEtiqueta($materiaId, $catedraId, false)) {
            $out[] = ['tema' => self::TEMA_FILTRO_SEM_ETIQUETA, 'parciais' => []];
        }

        return $out;
    }

    /**
     * Há pelo menos uma questão no escopo (matéria / cátedra / só demo) com `tema` vazio.
     */
    public static function hayQuestoesSemTemaEtiqueta(int $materiaId, ?int $catedraId, bool $demoOnly): bool
    {
        $q = Questao::query()
            ->where('materia_id', $materiaId)
            ->where(function ($w) {
                $w->whereNull('tema')->orWhere('tema', '');
            });

        if ($demoOnly) {
            $q->where('is_demo', true);
        }

        if ($catedraId !== null) {
            $q->where(function ($w) use ($catedraId) {
                $w->whereNull('catedra_id')->orWhere('catedra_id', $catedraId);
            });
        }

        return $q->exists();
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
}
