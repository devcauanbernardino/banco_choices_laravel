<?php

namespace App\Services\Questions;

/**
 * Mapeia origem_seccion do site farmaco2cat3choice → parcial (1|2|3|final|libre) e tema (rótulo).
 */
final class Farmaco2Cat3SectionCatalog
{
    public const MATERIA_ID = 5;

    public const MATERIA_SLUG = 'farmacologia-ii-catedra-3';

    public const CATEDRA_SLUG = 'catedra-iii';

    public const FONTE = 'farmaco2cat3choice.github.io';

    /**
     * @return array{parcial: string, tema: string, grupo: string}
     */
    public static function resolve(string $seccionId): array
    {
        $id = trim($seccionId);
        if (isset(self::MAP[$id])) {
            return self::MAP[$id];
        }

        return [
            'parcial' => '3',
            'tema' => self::humanize($id),
            'grupo' => 'Otros',
        ];
    }

    /** @return list<string> */
    public static function sectionIds(): array
    {
        return array_keys(self::MAP);
    }

    private static function humanize(string $id): string
    {
        $s = str_replace(['_', '-'], ' ', $id);

        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    /** @var array<string, array{parcial: string, tema: string, grupo: string}> */
    private const MAP = [
        // —— 1er parcial ——
        'inotropicos' => ['parcial' => '1', 'tema' => 'Inotrópicos', 'grupo' => '1er parcial · Cardiovascular'],
        'diureticos' => ['parcial' => '1', 'tema' => 'Diuréticos', 'grupo' => '1er parcial · Cardiovascular'],
        'antiarrítmicos' => ['parcial' => '1', 'tema' => 'Antiarrítmicos y Antiaginosos', 'grupo' => '1er parcial · Cardiovascular'],
        'antihipertensivos' => ['parcial' => '1', 'tema' => 'Antihipertensivos', 'grupo' => '1er parcial · Cardiovascular'],
        'antiagregantes' => ['parcial' => '1', 'tema' => 'Antiagregantes plaquetarios', 'grupo' => '1er parcial · Hemostasia'],
        'anticoagulantes' => ['parcial' => '1', 'tema' => 'Anticoagulantes', 'grupo' => '1er parcial · Hemostasia'],
        'fibrinoliticos' => ['parcial' => '1', 'tema' => 'Fibrinolíticos', 'grupo' => '1er parcial · Hemostasia'],
        'hipolipemiantes' => ['parcial' => '1', 'tema' => 'Hipolipemiantes, fibratos y resinas', 'grupo' => '1er parcial · Metabolismo'],
        'primerparcial' => ['parcial' => '1', 'tema' => 'Simulacro — 1er parcial', 'grupo' => '1er parcial · Simulacros'],
        'primerparcial_7_09_2023' => ['parcial' => '1', 'tema' => '1er parcial 07-09-2023', 'grupo' => '1er parcial · Simulacros'],
        'primerparcial_11_09_2024' => ['parcial' => '1', 'tema' => '1er parcial 11-09-2024', 'grupo' => '1er parcial · Simulacros'],
        'primerparcial_12_09_2024' => ['parcial' => '1', 'tema' => '1er parcial 12-09-2024', 'grupo' => '1er parcial · Simulacros'],

        // —— 2do parcial ——
        'generalidades_atb' => ['parcial' => '2', 'tema' => 'Generalidades de ATB', 'grupo' => '2do parcial · ATB'],
        'quinolonas' => ['parcial' => '2', 'tema' => 'Quinolonas', 'grupo' => '2do parcial · ATB'],
        'penicilinas' => ['parcial' => '2', 'tema' => 'Penicilinas', 'grupo' => '2do parcial · Pared celular'],
        'cefalosporinas' => ['parcial' => '2', 'tema' => 'Cefalosporinas', 'grupo' => '2do parcial · Pared celular'],
        'carbapenemos_y_monobactamicos' => ['parcial' => '2', 'tema' => 'Carbapenems y monobactámicos', 'grupo' => '2do parcial · Pared celular'],
        'inhib_B_lactamasa' => ['parcial' => '2', 'tema' => 'Inhibidores de β-lactamasas', 'grupo' => '2do parcial · Pared celular'],
        'otros_inhibidores_sintesis_pared' => ['parcial' => '2', 'tema' => 'Otros inhibidores de síntesis de pared', 'grupo' => '2do parcial · Pared celular'],
        'aminoglucosidos' => ['parcial' => '2', 'tema' => 'Aminoglucósidos', 'grupo' => '2do parcial · Síntesis proteica'],
        'tetraciclinas' => ['parcial' => '2', 'tema' => 'Tetraciclinas', 'grupo' => '2do parcial · Síntesis proteica'],
        'macrolidos' => ['parcial' => '2', 'tema' => 'Macrólidos', 'grupo' => '2do parcial · Síntesis proteica'],
        'otros_inhibidores_sintesis_proteinas' => ['parcial' => '2', 'tema' => 'Otros inhibidores de síntesis proteica', 'grupo' => '2do parcial · Síntesis proteica'],
        'sulfonamidas' => ['parcial' => '2', 'tema' => 'Sulfonamidas', 'grupo' => '2do parcial · Otros'],
        'antiparasitarios' => ['parcial' => '2', 'tema' => 'Antiparasitarios', 'grupo' => '2do parcial · Otros'],
        'antimicoticos' => ['parcial' => '2', 'tema' => 'Antimicóticos', 'grupo' => '2do parcial · Otros'],
        'antituberculosos' => ['parcial' => '2', 'tema' => 'Antituberculosos', 'grupo' => '2do parcial · Otros'],
        'antivirales' => ['parcial' => '2', 'tema' => 'Antivirales', 'grupo' => '2do parcial · Otros'],
        'antineoplasicos' => ['parcial' => '2', 'tema' => 'Antineoplásicos e inmunosupresores', 'grupo' => '2do parcial · Otros'],
        'segundoparcial' => ['parcial' => '2', 'tema' => 'Simulacro — 2do parcial', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_2024' => ['parcial' => '2', 'tema' => '2do parcial 2024', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_24_5_2024' => ['parcial' => '2', 'tema' => '2do parcial 24-05-2024', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_29_5_2024' => ['parcial' => '2', 'tema' => '2do parcial 29-05-2024', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_casos_clinicos_1' => ['parcial' => '2', 'tema' => 'Casos clínicos — 2do parcial (1)', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_casos_clinicos_2' => ['parcial' => '2', 'tema' => 'Casos clínicos — 2do parcial (2)', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_casos_clinicos_3' => ['parcial' => '2', 'tema' => 'Casos clínicos — 2do parcial (3)', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_casos_clinicos_4' => ['parcial' => '2', 'tema' => 'Casos clínicos — 2do parcial (4)', 'grupo' => '2do parcial · Simulacros'],
        'segundoparcial_casos_clinicos_5' => ['parcial' => '2', 'tema' => 'Casos clínicos — 2do parcial (5)', 'grupo' => '2do parcial · Simulacros'],

        // —— 3er parcial ——
        'antisicoticos' => ['parcial' => '3', 'tema' => 'Antipsicóticos', 'grupo' => '3er parcial · SNC'],
        'antiparkinsonianos' => ['parcial' => '3', 'tema' => 'Antiparkinsonianos', 'grupo' => '3er parcial · SNC'],
        'antidepresivos' => ['parcial' => '3', 'tema' => 'Antidepresivos', 'grupo' => '3er parcial · SNC'],
        'litio' => ['parcial' => '3', 'tema' => 'Litio', 'grupo' => '3er parcial · SNC'],
        'ansioliticos' => ['parcial' => '3', 'tema' => 'Hipnóticos y ansiolíticos', 'grupo' => '3er parcial · SNC'],
        'anticonvulsivantes' => ['parcial' => '3', 'tema' => 'Anticonvulsivantes', 'grupo' => '3er parcial · SNC'],
        'digestivo' => ['parcial' => '3', 'tema' => 'Digestivo', 'grupo' => '3er parcial · Sistemas'],
        'respiratorio' => ['parcial' => '3', 'tema' => 'Respiratorio', 'grupo' => '3er parcial · Sistemas'],
        'tercerparcial_2024' => ['parcial' => '3', 'tema' => '3er parcial 2024', 'grupo' => '3er parcial · Simulacros'],
        'tercerparcial_27_6_2024' => ['parcial' => '3', 'tema' => '3er parcial 27-06-2024', 'grupo' => '3er parcial · Simulacros'],
        'tercerparcial_23_11_2023' => ['parcial' => '3', 'tema' => '3er parcial 23-11-2023', 'grupo' => '3er parcial · Simulacros'],
        'tercerparcial_29_6_2023' => ['parcial' => '3', 'tema' => '3er parcial 29-06-2023', 'grupo' => '3er parcial · Simulacros'],

        // —— Finales y libre ——
        'final_22_11_2023' => ['parcial' => 'final', 'tema' => 'Final 22-11-2023', 'grupo' => 'Finales'],
        'final_20_09_2023' => ['parcial' => 'final', 'tema' => 'Final 20-09-2023', 'grupo' => 'Finales'],
        'final_10_07_2023' => ['parcial' => 'final', 'tema' => 'Final 10-07-2023', 'grupo' => 'Finales'],
        'final_17_07_2024' => ['parcial' => 'final', 'tema' => 'Final 17-07-2024', 'grupo' => 'Finales'],
        'final_10_07_2024' => ['parcial' => 'final', 'tema' => 'Final 10-07-2024', 'grupo' => 'Finales'],
        'examenlibre' => ['parcial' => 'libre', 'tema' => 'Examen libre', 'grupo' => 'Examen libre'],
    ];
}
