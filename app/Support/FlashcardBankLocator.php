<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Resolve o arquivo de flashcards (storage/app/data/flashcards_*.json) de uma materia,
 * no mesmo espirito do QuestionBankLocator — mas o conteudo aqui e proprio (cartoes de
 * definicao gerados a parte), nao reaproveita o banco de questoes de multipla escolha.
 */
final class FlashcardBankLocator
{
    public static function filenameFor(int $materiaId): string
    {
        return match ($materiaId) {
            11 => 'flashcards_imunologia.json',
            default => self::filenameFromSlug($materiaId) ?? 'flashcards_materia_'.$materiaId.'.json',
        };
    }

    private static function filenameFromSlug(int $materiaId): ?string
    {
        $slug = DB::table('materias')->where('id', $materiaId)->value('slug');
        if (! is_string($slug) || trim($slug) === '') {
            return null;
        }

        $candidate = 'flashcards_'.str_replace('-', '_', $slug).'.json';

        return is_file(storage_path('app/data/'.$candidate)) ? $candidate : null;
    }

    public static function resolvePath(int $materiaId): string
    {
        return storage_path('app/data/'.self::filenameFor($materiaId));
    }

    public static function hasBank(int $materiaId): bool
    {
        return self::loadList($materiaId) !== [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function loadList(int $materiaId): array
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

        if (isset($data['flashcards']) && is_array($data['flashcards'])) {
            return array_values(array_filter($data['flashcards'], 'is_array'));
        }

        if (array_is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        return [];
    }
}
