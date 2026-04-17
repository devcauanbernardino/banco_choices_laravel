<?php

namespace App\Support;

/**
 * Ícone Material Symbols por nome da matéria — alinhado ao mock docs/layouts/selecionar_mat_rias.
 */
final class MateriaLayout
{
    public static function materialIcon(string $nome): string
    {
        $n = mb_strtolower($nome);

        return match (true) {
            str_contains($n, 'cardio') => 'cardiology',
            str_contains($n, 'neuro') => 'neurology',
            str_contains($n, 'pediat') => 'child_care',
            str_contains($n, 'gine') || str_contains($n, 'obst') => 'female',
            str_contains($n, 'trauma') || str_contains($n, 'ortop') => 'orthopedics',
            str_contains($n, 'nefro') => 'nephrology',
            str_contains($n, 'psiq') || str_contains($n, 'psic') => 'psychiatry',
            str_contains($n, 'infect') || str_contains($n, 'micro') => 'microbiology',
            str_contains($n, 'anat') => 'anatomy',
            str_contains($n, 'bio') => 'genetics',
            default => 'medical_services',
        };
    }

    /** Chave i18n signup.materia.hint.* */
    public static function hintKey(string $nome): string
    {
        $n = mb_strtolower($nome);

        return match (true) {
            str_contains($n, 'anat') => 'anat',
            str_contains($n, 'micro') => 'micro',
            str_contains($n, 'bio') => 'bio',
            default => 'default',
        };
    }
}
