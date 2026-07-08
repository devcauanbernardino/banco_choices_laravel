<?php

namespace App\Support;

/**
 * Cartoes de flashcards tem o texto de referencia em espanhol (es).
 * Traducoes por idioma: array JSON simples, indice 0..N (mesma posicao do
 * overlay_key usado por FlashcardBankLocator/FlashcardController), cada item
 * { "frente", "verso" }.
 * - storage/app/data/i18n/{locale}/{nome_do_ficheiro_de_flashcards}
 */
class FlashcardLocale
{
    /** @var array<string, list<array<string, mixed>>> */
    private static array $overlayCache = [];

    public static function apply(array $carta, int $overlayKey, string $locale, string $bankFilename): array
    {
        if ($bankFilename === '' || self::isSpanishLocale($locale)) {
            return $carta;
        }

        $overlay = self::loadOverlay(QuestionLocale::normalizeLocaleKey($locale), $bankFilename);
        $patch = $overlay[$overlayKey] ?? null;
        if (! is_array($patch)) {
            return $carta;
        }

        $out = $carta;

        if (isset($patch['frente']) && is_string($patch['frente']) && $patch['frente'] !== '') {
            $out['frente'] = $patch['frente'];
        }

        if (isset($patch['verso']) && is_string($patch['verso']) && $patch['verso'] !== '') {
            $out['verso'] = $patch['verso'];
        }

        return $out;
    }

    private static function isSpanishLocale(string $locale): bool
    {
        return str_starts_with(strtolower(str_replace('-', '_', $locale)), 'es');
    }

    /**
     * @return list<string>
     */
    private static function overlayCandidatePaths(string $localeKey, string $bankFilename): array
    {
        return [
            storage_path('app/data/i18n/'.$localeKey.'/'.$bankFilename),
            resource_path('question-i18n/'.$localeKey.'/'.$bankFilename),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function loadOverlay(string $localeKey, string $bankFilename): array
    {
        $key = $localeKey.'|'.$bankFilename;
        if (isset(self::$overlayCache[$key])) {
            return self::$overlayCache[$key];
        }

        $json = null;
        foreach (self::overlayCandidatePaths($localeKey, $bankFilename) as $path) {
            if (! is_file($path)) {
                continue;
            }

            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $json = $decoded;

                break;
            }
        }

        if (! is_array($json)) {
            self::$overlayCache[$key] = [];

            return [];
        }

        if (isset($json['flashcards']) && is_array($json['flashcards'])) {
            $json = $json['flashcards'];
        }

        self::$overlayCache[$key] = array_values($json);

        return self::$overlayCache[$key];
    }
}
