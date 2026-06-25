<?php

namespace App\Services\Questions;

/**
 * Mapeia origem_seccion do compilado Medimisión (Farmacología I — Cátedra III, 1er Parcial) → parcial e tema.
 */
final class Farmaco1Cat3SectionCatalog
{
    public const MATERIA_ID = 10;

    public const MATERIA_SLUG = 'farmacologia-i';

    public const CATEDRA_SLUG = 'catedra-iii';

    public const FONTE = 'Medimisión — Farmacología I, Cátedra III, 1er Parcial (1ra ed., I Cuatrimestre 2025)';

    /**
     * @return array{parcial: string, tema: string}
     */
    public static function resolve(string $seccionId): array
    {
        $id = trim($seccionId);
        if (isset(self::MAP[$id])) {
            return self::MAP[$id];
        }

        return [
            'parcial' => '1',
            'tema' => self::humanize($id),
        ];
    }

    private static function humanize(string $id): string
    {
        $s = str_replace(['_', '-'], ' ', $id);

        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    /** @var array<string, array{parcial: string, tema: string}> */
    private const MAP = [
        'farmacocinetica_i' => ['parcial' => '1', 'tema' => 'TP1 — Farmacocinética I'],
        'farmacocinetica_ii_parte1' => ['parcial' => '1', 'tema' => 'TP2 — Farmacocinética II (Parte I)'],
        'farmacocinetica_ii_parte2' => ['parcial' => '1', 'tema' => 'TP2 — Farmacocinética II (Parte II)'],
        'farmacodinamia_parte1' => ['parcial' => '1', 'tema' => 'TP3 — Farmacodinamia (Parte I)'],
        'farmacodinamia_parte2' => ['parcial' => '1', 'tema' => 'TP3 — Farmacodinamia (Parte II)'],
        'farmacogenetica_biotec1' => ['parcial' => '1', 'tema' => 'TP4 — Farmacogenética y Fármacos Biotecnológicos I'],
    ];
}
