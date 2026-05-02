<?php

namespace App\Support;

/**
 * Ficheiros de questões têm o texto de referência em espanhol (es).
 * Traduções por idioma (mapa chave => { "pergunta", "opcoes", "feedback" }):
 * chave = índice 0..N no JSON (`_overlay_key`, definido ao carregar o banco) ou, em overlays antigos, `numero`.
 * - storage/app/data/i18n/{locale}/{nome_do_ficheiro_base} (gerado em deploy com questions:build-i18n)
 * - resources/question-i18n/{locale}/… (opcional, para versionar no git)
 */
class QuestionLocale
{
    /** @var array<string, array<string, mixed>> */
    private static array $overlayCache = [];

    public static function apply(array $questao, string $locale, string $bankFilename): array
    {
        if ($bankFilename === '') {
            return $questao;
        }

        if (self::isSpanishLocale($locale)) {
            return $questao;
        }

        $overlay = self::loadOverlay(self::normalizeLocaleKey($locale), $bankFilename);
        if ($overlay === []) {
            return $questao;
        }

        $patch = null;
        foreach (self::overlayLookupKeys($questao) as $lookupKey) {
            $patch = self::overlayPatchForKey($overlay, $lookupKey);
            if ($patch !== null) {
                break;
            }
        }
        if ($patch === null) {
            return $questao;
        }

        $out = $questao;

        if (isset($patch['pergunta']) && is_string($patch['pergunta']) && $patch['pergunta'] !== '') {
            $out['pergunta'] = $patch['pergunta'];
        }

        if (isset($patch['feedback']) && is_string($patch['feedback']) && $patch['feedback'] !== '') {
            $out['feedback'] = $patch['feedback'];
        }

        if (isset($patch['opcoes']) && is_array($patch['opcoes'])) {
            $out['opcoes'] = self::mergeOpcoes($questao['opcoes'] ?? [], $patch['opcoes']);
        }

        if (isset($patch['nota']) && is_string($patch['nota']) && $patch['nota'] !== '') {
            $out['nota'] = $patch['nota'];
        }

        return $out;
    }

    public static function clearCache(): void
    {
        self::$overlayCache = [];
    }

    /**
     * Indica se existe ficheiro de tradução não vazio para o banco (pt_BR / en_US em storage ou resources).
     * Locales em espanhol consideram-se sempre satisfeitos (texto de referência do JSON).
     */
    public static function hasTranslationOverlay(string $locale, string $bankFilename): bool
    {
        if ($bankFilename === '' || self::isSpanishLocale($locale)) {
            return true;
        }

        $localeKey = self::normalizeLocaleKey($locale);
        foreach (self::overlayCandidatePaths($localeKey, $bankFilename) as $path) {
            if (! is_file($path) || filesize($path) < 32) {
                continue;
            }

            $decoded = json_decode((string) file_get_contents($path), true);
            if (! is_array($decoded) || $decoded === []) {
                continue;
            }

            if (isset($decoded['questoes']) && is_array($decoded['questoes'])) {
                $decoded = $decoded['questoes'];
            }

            foreach ($decoded as $block) {
                if (is_array($block) && $block !== []) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function normalizeLocaleKey(string $locale): string
    {
        $l = str_replace('-', '_', $locale);
        if (in_array($l, ['pt', 'en'], true)) {
            return match ($l) {
                'pt' => 'pt_BR',
                'en' => 'en_US',
                default => $l,
            };
        }

        return $l;
    }

    private static function isSpanishLocale(string $locale): bool
    {
        $l = strtolower(str_replace('-', '_', $locale));

        return str_starts_with($l, 'es');
    }

    /**
     * Prioridade: `_overlay_key` (índice estável no ficheiro do banco), depois `numero` (overlays antigos).
     *
     * @return list<string>
     */
    private static function overlayLookupKeys(array $questao): array
    {
        $keys = [];
        if (array_key_exists('_overlay_key', $questao)) {
            $keys[] = (string) $questao['_overlay_key'];
        }
        if (isset($questao['numero']) && $questao['numero'] !== '' && $questao['numero'] !== null) {
            $keys[] = (string) $questao['numero'];
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param  array<string|int, array<string, mixed>>  $overlay
     */
    private static function overlayPatchForKey(array $overlay, string $key): ?array
    {
        if (isset($overlay[$key]) && is_array($overlay[$key])) {
            return $overlay[$key];
        }

        if (ctype_digit($key)) {
            $i = (int) $key;
            if (isset($overlay[$i]) && is_array($overlay[$i])) {
                return $overlay[$i];
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>|array<string, string>  $rawOpcoes
     * @param  list<string>  $textos
     * @return list<array<string, mixed>>
     */
    private static function mergeOpcoes(array $rawOpcoes, array $textos): array
    {
        if ($rawOpcoes === [] || $textos === []) {
            return $rawOpcoes;
        }

        $first = reset($rawOpcoes);
        if (is_array($first) && (isset($first['texto']) || isset($first['text']))) {
            $out = [];
            $i = 0;
            foreach ($rawOpcoes as $op) {
                if (! is_array($op)) {
                    continue;
                }
                $t = $textos[$i] ?? null;
                $item = $op;
                if (is_string($t) && $t !== '') {
                    $item['texto'] = $t;
                }
                $out[] = $item;
                $i++;
            }

            return $out;
        }

        // Lista simples de strings (mesma ordem que no overlay)
        $letters = ['A', 'B', 'C', 'D', 'E'];
        $out = [];
        $i = 0;
        foreach ($rawOpcoes as $op) {
            if (! is_string($op)) {
                continue;
            }
            $t = $textos[$i] ?? null;
            $text = (is_string($t) && $t !== '') ? $t : $op;
            $out[] = [
                'letra' => $letters[$i] ?? chr(ord('A') + $i),
                'texto' => $text,
            ];
            $i++;
        }

        if ($out === [] && $rawOpcoes !== []) {
            return $rawOpcoes;
        }

        return $out;
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
     * @return array<string, array<string, mixed>> mapa chave (índice ou numero) => dados
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

        if (isset($json['questoes']) && is_array($json['questoes'])) {
            $json = $json['questoes'];
        }

        self::$overlayCache[$key] = $json;

        return $json;
    }
}
