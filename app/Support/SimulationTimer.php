<?php

namespace App\Support;

/**
 * Cronômetro do modo exame. Tempo padrão de 60 minutos, mas o usuário pode
 * personalizar entre MIN_MINUTES e MAX_MINUTES ao criar o simulado.
 */
class SimulationTimer
{
    public const DEFAULT_SECONDS = 3600;

    public const MIN_MINUTES = 5;

    public const MAX_MINUTES = 180;

    /** Converte minutos (já validados) para segundos, com fallback para o padrão. */
    public static function secondsFromMinutes(?int $minutos): int
    {
        if ($minutos === null || $minutos < self::MIN_MINUTES || $minutos > self::MAX_MINUTES) {
            return self::DEFAULT_SECONDS;
        }

        return $minutos * 60;
    }

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
