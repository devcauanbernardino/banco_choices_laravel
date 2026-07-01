<?php

namespace App\Services\Flashcards;

use App\Models\FlashcardProgresso;
use App\Models\Questao;

class FlashcardQueueBuilder
{
    public const DEFAULT_NEW_CARDS_PER_DAY = 20;

    /**
     * @return array{
     *     due: list<array{questao_id: int, overlay_key: int}>,
     *     new: list<array{questao_id: int, overlay_key: int}>,
     *     due_count: int,
     *     new_count: int,
     *     new_available_count: int
     * }
     */
    public static function buildQueue(int $userId, int $materiaId, int $novosPorDia = self::DEFAULT_NEW_CARDS_PER_DAY): array
    {
        $questoes = Questao::query()
            ->where('materia_id', $materiaId)
            ->orderBy('overlay_key')
            ->get(['id', 'overlay_key']);

        $progressoPorQuestao = FlashcardProgresso::query()
            ->where('usuario_id', $userId)
            ->where('materia_id', $materiaId)
            ->get()
            ->keyBy('questao_id');

        $now = now();
        $due = [];
        $new = [];

        foreach ($questoes as $q) {
            /** @var FlashcardProgresso|null $p */
            $p = $progressoPorQuestao->get($q->id);

            if ($p === null) {
                $new[] = ['questao_id' => $q->id, 'overlay_key' => (int) $q->overlay_key];

                continue;
            }

            if ($p->proxima_revisao_em !== null && $p->proxima_revisao_em->lte($now)) {
                $due[] = [
                    'questao_id' => $q->id,
                    'overlay_key' => (int) $q->overlay_key,
                    '_proxima' => $p->proxima_revisao_em,
                ];
            }
        }

        usort($due, fn ($a, $b) => $a['_proxima'] <=> $b['_proxima']);
        $due = array_map(fn ($d) => ['questao_id' => $d['questao_id'], 'overlay_key' => $d['overlay_key']], $due);

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
