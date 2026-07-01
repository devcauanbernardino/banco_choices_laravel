<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

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
            'questoes_farmaco1_cat3.json',
            'questoes_neurocirugia.json',
            'questoes_ciencias_sociales_y_medicina.json',
            'questoes_farmacologia_general_barcelo.json',
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
            13 => 'questoes_farmaco1_cat3.json',
            16 => 'questoes_bioetica_2024_2025.json',
            default => self::filenameFromSlug($materiaId) ?? 'questoes_materia_'.$materiaId.'.json',
        };
    }

    /**
     * Resolve um ficheiro pelo slug da matéria (questoes_<slug-com-underscores>.json),
     * para que matérias novas não dependam do id auto-incremental coincidir entre ambientes.
     */
    private static function filenameFromSlug(int $materiaId): ?string
    {
        $slug = DB::table('materias')->where('id', $materiaId)->value('slug');
        if (! is_string($slug) || trim($slug) === '') {
            return null;
        }

        $candidate = 'questoes_'.str_replace('-', '_', $slug).'.json';

        return is_file(storage_path('app/data/'.$candidate)) ? $candidate : null;
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
        $ids = DB::table('materias')->pluck('id')->map(fn ($v) => (int) $v)->all();

        return self::filterIdsWithBank($ids);
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
