<?php

namespace App\Support;

/**
 * Cronômetro do modo exame (60 minutos).
 */
class SimulationTimer
{
    public const DEFAULT_SECONDS = 3600;

    public static function remainingSeconds(?int $inicioSegundosEpoch, int $totalSeconds = self::DEFAULT_SECONDS): int
    {
        if ($inicioSegundosEpoch === null) {
            return $totalSeconds;
        }

        return max(0, $totalSeconds - (time() - $inicioSegundosEpoch));
    }

    public static function isExpired(?int $inicioSegundosEpoch, int $totalSeconds = self::DEFAULT_SECONDS): bool
    {
        return self::remainingSeconds($inicioSegundosEpoch, $totalSeconds) <= 0;
    }
}
