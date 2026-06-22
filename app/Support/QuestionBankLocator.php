<?php

namespace App\Support;

final class QuestionBankLocator
{
    /** @return list<string> */
    public static function allKnownFilenames(): array
    {
        return [
            'questoes_microbiologia_refinado.json',
            'questoes_biologia_final_v2.json',
            'biologia_alumed_1parcial.json',
            'biologia_cbc_1parcial_2022.json',
            'questoes_farmaco2_cat3.json',
            'questoes_fisiologia_2022.json',
            'questoes_imunologia_2024_2025.json',
            'questoes_patologia_2024.json',
            'questoes_bioetica_2024_2025.json',
        ];
    }

    public static function filenameFor(int $materiaId): string
    {
        return match ($materiaId) {
            1 => 'questoes_microbiologia_refinado.json',
            2 => 'questoes_biologia_final_v2.json',
            3 => 'biologia_alumed_1parcial.json',
            4 => 'biologia_cbc_1parcial_2022.json',
            5 => 'questoes_farmaco2_cat3.json',
            9 => 'questoes_fisiologia_2022.json',
            11 => 'questoes_imunologia_2024_2025.json',
            12 => 'questoes_patologia_2024.json',
            16 => 'questoes_bioetica_2024_2025.json',
            default => 'questoes_materia_'.$materiaId.'.json',
        };
    }

    public static function resolvePath(int $materiaId): string
    {
        return storage_path('app/data/'.self::filenameFor($materiaId));
    }

    public static function hasBank(int $materiaId): bool
    {
        return self::loadCanonicalList($materiaId) !== [];
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    public static function filterIdsWithBank(array $ids): array
    {
        return array_values(array_filter(
            $ids,
            fn (int $id) => $id > 0 && self::hasBank($id)
        ));
    }

    /** @return list<int> */
    public static function allMateriaIdsWithBank(): array
    {
        return self::filterIdsWithBank([1, 2, 3, 4, 5, 9, 11, 12, 16]);
    }

    /**
     * Lista canónica ordenada pela posição original no JSON (para overlay_key).
     *
     * @return list<array<string, mixed>>
     */
    public static function loadCanonicalList(int $materiaId): array
    {
        $path = self::resolvePath($materiaId);
        if (! is_readable($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return [];
        }

        if (isset($data['questoes']) && is_array($data['questoes'])) {
            return array_values(array_filter($data['questoes'], 'is_array'));
        }

        if (array_is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        return [];
    }
}
