<?php

namespace App\Services\Questions;

use App\Support\Question;

/**
 * Sugere um tema editorial por questão usando uma taxonomia fixa (tema → lista de palavras‑chave).
 * Não substitui revisão humana; útil para pré‑etiquetar ou auditar o banco JSON.
 */
final class QuestionTemaSuggester
{
    /**
     * @param  array<string, mixed>  $questionBlob
     * @param  array<string, array<int, string>|string>  $taxonomy  tema => [keywords] ou string única
     * @return array{tema: ?string, score: float, hits: list<string>, texto_usado: string}
     */
    public static function suggest(array $questionBlob, array $taxonomy): array
    {
        $texto = self::blobTextoCompleto($questionBlob);
        $norm = self::normalizar($texto);

        $bestTema = null;
        $bestScore = 0.0;
        $bestHits = [];

        foreach ($taxonomy as $temaNome => $keywords) {
            $lista = is_array($keywords) ? $keywords : [$keywords];
            $hits = [];
            $score = 0.0;

            foreach ($lista as $kw) {
                $k = trim((string) $kw);
                if ($k === '') {
                    continue;
                }
                $nk = self::normalizar($k);
                if ($nk === '') {
                    continue;
                }
                $n = self::contarOcorrencias($norm, $nk);
                if ($n > 0) {
                    $hits[] = $k;
                    $score += $n * (mb_strlen($nk, 'UTF-8') >= 8 ? 1.4 : 1.0);
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestTema = (string) $temaNome;
                $bestHits = $hits;
            }
        }

        return [
            'tema' => $bestScore > 0 ? $bestTema : null,
            'score' => round($bestScore, 3),
            'hits' => array_values(array_unique($bestHits)),
            'texto_usado' => $texto,
        ];
    }

    /**
     * @param  array<string, mixed>  $questionBlob
     */
    public static function blobTextoCompleto(array $questionBlob): string
    {
        $qn = new Question($questionBlob);
        $partes = [$qn->getPergunta()];
        foreach ($qn->getOpcoes() as $op) {
            $partes[] = $op;
        }
        foreach (['feedback', 'nota', 'referencia', 'fonte'] as $campo) {
            if (! empty($questionBlob[$campo]) && is_string($questionBlob[$campo])) {
                $partes[] = $questionBlob[$campo];
            }
        }

        return trim(implode("\n", array_filter($partes, fn ($p) => is_string($p) && trim($p) !== '')));
    }

    public static function normalizar(string $s): string
    {
        if ($s === '') {
            return '';
        }
        if (class_exists(\Normalizer::class)) {
            $s = \Normalizer::normalize($s, \Normalizer::FORM_D) ?? $s;
            $s = preg_replace('/\p{Mn}/u', '', $s) ?? $s;
        }
        $s = mb_strtolower($s, 'UTF-8');

        return trim($s);
    }

    private static function contarOcorrencias(string $haystackNorm, string $needleNorm): int
    {
        if ($needleNorm === '' || $haystackNorm === '') {
            return 0;
        }
        $n = 0;
        $offset = 0;
        while (($pos = mb_stripos($haystackNorm, $needleNorm, $offset, 'UTF-8')) !== false) {
            $n++;
            $offset = $pos + mb_strlen($needleNorm, 'UTF-8');
        }

        return $n;
    }
}
