<?php

namespace App\Support;

final class QuestionBankLocator
{
    public static function filenameFor(int $materiaId): string
    {
        return match ($materiaId) {
            1 => 'questoes_microbiologia_refinado.json',
            2 => 'questoes_biologia_final_v2.json',
            default => 'questoes_materia_'.$materiaId.'.json',
        };
    }

    public static function resolvePath(int $materiaId): string
    {
        return storage_path('app/data/'.self::filenameFor($materiaId));
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
