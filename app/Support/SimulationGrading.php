<?php

namespace App\Support;

final class SimulationGrading
{
    /** Percentual mínimo de acertos para considerar o simulado aprovado. */
    public const APROVACAO_PCT = 60;

    /** @param  float  $percentual  De 0 a 100. */
    public static function aprovado(float $percentual): bool
    {
        return $percentual >= self::APROVACAO_PCT;
    }
}
