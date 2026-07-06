<?php

namespace App\Services\Decks;

use App\Models\DeckCarta;
use App\Models\DeckProgresso;

class DeckQueueBuilder
{
    public const DEFAULT_NEW_CARDS_PER_DAY = 20;

    /**
     * @return array{
     *     due: list<int>,
     *     new: list<int>,
     *     due_count: int,
     *     new_count: int,
     *     new_available_count: int
     * }
     */
    public static function buildQueue(int $userId, int $deckId, int $novosPorDia = self::DEFAULT_NEW_CARDS_PER_DAY): array
    {
        $cartas = DeckCarta::query()
            ->where('deck_id', $deckId)
            ->orderBy('ordem')
            ->get(['id']);

        $progressoPorCarta = DeckProgresso::query()
            ->where('usuario_id', $userId)
            ->whereIn('deck_carta_id', $cartas->pluck('id'))
            ->get()
            ->keyBy('deck_carta_id');

        $now = now();
        $due = [];
        $new = [];

        foreach ($cartas as $c) {
            /** @var DeckProgresso|null $p */
            $p = $progressoPorCarta->get($c->id);

            if ($p === null) {
                $new[] = $c->id;

                continue;
            }

            if ($p->proxima_revisao_em !== null && $p->proxima_revisao_em->lte($now)) {
                $due[] = ['id' => $c->id, '_proxima' => $p->proxima_revisao_em];
            }
        }

        usort($due, fn ($a, $b) => $a['_proxima'] <=> $b['_proxima']);
        $due = array_map(fn ($d) => $d['id'], $due);

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
    public static function counts(int $userId, int $deckId, int $novosPorDia = self::DEFAULT_NEW_CARDS_PER_DAY): array
    {
        $result = self::buildQueue($userId, $deckId, $novosPorDia);

        unset($result['due'], $result['new']);

        return $result;
    }
}
