<?php

namespace App\Services\Flashcards;

use App\Models\FlashcardProgresso;
use App\Support\FlashcardBankLocator;

class FlashcardQueueBuilder
{
    public const DEFAULT_NEW_CARDS_PER_DAY = 20;

    /**
     * @param  list<string>  $temas  Se nao vazio, restringe a fila aos cartoes marcados com um desses temas.
     * @return array{
     *     due: list<array{overlay_key: int}>,
     *     new: list<array{overlay_key: int}>,
     *     due_count: int,
     *     new_count: int,
     *     new_available_count: int
     * }
     */
    public static function buildQueue(int $userId, int $materiaId, int $novosPorDia = self::DEFAULT_NEW_CARDS_PER_DAY, array $temas = []): array
    {
        $overlayKeys = self::eligibleOverlayKeys($materiaId, $temas);

        $progressoPorOverlay = FlashcardProgresso::query()
            ->where('usuario_id', $userId)
            ->where('materia_id', $materiaId)
            ->get()
            ->keyBy('overlay_key');

        $now = now();
        $due = [];
        $new = [];

        foreach ($overlayKeys as $overlayKey) {
            /** @var FlashcardProgresso|null $p */
            $p = $progressoPorOverlay->get($overlayKey);

            if ($p === null) {
                $new[] = ['overlay_key' => $overlayKey];

                continue;
            }

            if ($p->proxima_revisao_em !== null && $p->proxima_revisao_em->lte($now)) {
                $due[] = [
                    'overlay_key' => $overlayKey,
                    '_proxima' => $p->proxima_revisao_em,
                ];
            }
        }

        usort($due, fn ($a, $b) => $a['_proxima'] <=> $b['_proxima']);
        $due = array_map(fn ($d) => ['overlay_key' => $d['overlay_key']], $due);

        $newAvailableCount = count($new);
        $new = array_slice($new, 0, max(0, $novosPorDia));

        return [
            'due' => array_values($due),
            'new' => array_values($new),
            'due_count' => count($due),
            'new_count' => count($new),
            'new_available_count' => $newAvailableCount,
        ];
    }

    /**
     * @return array{due_count: int, new_count: int, new_available_count: int}
     */
    public static function counts(int $userId, int $materiaId, int $novosPorDia = self::DEFAULT_NEW_CARDS_PER_DAY): array
    {
        $result = self::buildQueue($userId, $materiaId, $novosPorDia);

        unset($result['due'], $result['new']);

        return $result;
    }

    /**
     * Fila com todos os cartoes da materia (ou do(s) tema(s) filtrado(s)), sem distincao
     * due/novo e sem SM-2 — usada pelo modo de navegacao livre.
     *
     * @param  list<string>  $temas
     * @return list<array{overlay_key: int}>
     */
    public static function buildFreeBrowseQueue(int $materiaId, array $temas = []): array
    {
        return array_map(
            static fn (int $overlayKey) => ['overlay_key' => $overlayKey],
            self::eligibleOverlayKeys($materiaId, $temas)
        );
    }

    /**
     * @param  list<string>  $temas
     * @return list<int>
     */
    private static function eligibleOverlayKeys(int $materiaId, array $temas): array
    {
        $lista = FlashcardBankLocator::loadList($materiaId);

        if ($temas === []) {
            return array_keys($lista);
        }

        $out = [];
        foreach ($lista as $overlayKey => $carta) {
            if (in_array((string) ($carta['tema'] ?? ''), $temas, true)) {
                $out[] = $overlayKey;
            }
        }

        return $out;
    }
}
