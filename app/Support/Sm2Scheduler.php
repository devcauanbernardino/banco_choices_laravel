<?php

namespace App\Support;

/**
 * Algoritmo SM-2 (SuperMemo 2) para repetição espaçada — o mesmo usado historicamente pelo Anki.
 */
final class Sm2Scheduler
{
    public const QUALITY_AGAIN = 0;

    public const QUALITY_HARD = 3;

    public const QUALITY_GOOD = 4;

    public const QUALITY_EASY = 5;

    public const MIN_EASE_FACTOR = 1.3;

    public const DEFAULT_EASE_FACTOR = 2.5;

    /**
     * @return array{ease_factor: float, interval_days: int, repetitions: int}
     */
    public static function next(float $easeFactor, int $intervalDays, int $repetitions, int $quality): array
    {
        $q = max(0, min(5, $quality));

        $ef = $easeFactor + (0.1 - (5 - $q) * (0.08 + (5 - $q) * 0.02));
        $ef = max(self::MIN_EASE_FACTOR, round($ef, 2));

        if ($q < 3) {
            return ['ease_factor' => $ef, 'interval_days' => 1, 'repetitions' => 0];
        }

        $reps = $repetitions + 1;
        $interval = match (true) {
            $reps === 1 => 1,
            $reps === 2 => 6,
            default => (int) round($intervalDays * $ef),
        };

        return ['ease_factor' => $ef, 'interval_days' => max(1, $interval), 'repetitions' => $reps];
    }

    public static function buttonToQuality(string $button): int
    {
        return match ($button) {
            'again' => self::QUALITY_AGAIN,
            'hard' => self::QUALITY_HARD,
            'good' => self::QUALITY_GOOD,
            'easy' => self::QUALITY_EASY,
            default => throw new \InvalidArgumentException("Avaliação inválida: {$button}"),
        };
    }
}
