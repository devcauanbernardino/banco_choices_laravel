<?php

namespace App\Services\Flashcards;

use App\Models\FlashcardProgresso;
use App\Support\FlashcardBankLocator;

class FlashcardQueueBuilder
{
    public const DEFAULT_NEW_CARDS_PER_DAY = 20;

    /**
     * @return array{
     *     due: list<array{overlay_key: int}>,
     *     new: list<array{overlay_key: int}>,
     *     due_count: int,
     *     new_count: int,
     *     new_available_count: int
     * }
     */
    public static function buildQueue(int $userId, int $materiaId, int $novosPorDia = self::DEFAULT_NEW_CARDS_PER_DAY): array
    {
        $total = count(FlashcardBankLocator::loadList($materiaId));

        $progressoPorOverlay = FlashcardProgresso::query()
            ->where('usuario_id', $userId)
            ->where('materia_id', $materiaId)
            ->get()
            ->keyBy('overlay_key');

        $now = now();
        $due = [];
        $new = [];

        for ($overlayKey = 0; $overlayKey < $total; $overlayKey++) {
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
}
