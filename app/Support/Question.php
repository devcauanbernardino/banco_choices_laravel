<?php

namespace App\Support;

class Question
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getCorrectAnswer(): string
    {
        $raw = $this->data['resposta_correta'] ?? $this->data['gabarito'] ?? $this->data['correta'] ?? '';
        if ($raw === '' || $raw === null) {
            return '';
        }
        $v = trim((string) $raw);
        if (preg_match('/^[A-E]$/i', $v)) {
            return (string) (ord(strtoupper($v)) - ord('A'));
        }

        return $v;
    }

    public function getFeedback(): string
    {
        $raw = $this->data['feedback'] ?? '';

        if (self::editorialFeedbackPresent(is_string($raw) ? $raw : null)) {
            return trim((string) $raw);
        }

        return self::synthesizeFeedbackEs($this->data);
    }

    /**
     * Indica se o campo feedback existe e não é só placeholder genérico.
     */
    public static function editorialFeedbackPresent(?string $feedback): bool
    {
        $trim = trim((string) $feedback);
        if ($trim === '') {
            return false;
        }

        $placeholders = [
            'Sem explicação disponível',
            'Sem explicacao disponivel',
            'Sin explicación disponible',
            'Sin explicacion disponible',
            'No explanation available',
            'Feedback não disponível devido a erro na geração.',
            'Feedback nao disponivel devido a erro na geracao.',
        ];

        foreach ($placeholders as $p) {
            if (strcasecmp($trim, $p) === 0) {
                return false;
            }
        }

        // Mensagens de falha de geração automática nos JSON (PT), várias redações
        $tlower = mb_strtolower($trim);
        if (str_starts_with($tlower, 'feedback ')
            && str_contains($tlower, 'erro')
            && preg_match('/gera[cç][aão]|geracao/u', $tlower)) {
            return false;
        }

        return true;
    }

    /**
     * Feedback editorial ausente ou só placeholder — gera texto útil a partir da opção correta (espanhol, idioma do banco).
     */
    public static function synthesizeFeedbackEs(array $data): string
    {
        $q = new self($data);
        $opts = $q->getOpcoes();
        $idxStr = $q->getCorrectAnswer();

        if ($opts === []) {
            return 'Esta pregunta no tiene alternativas válidas en el banco de datos.';
        }

        if ($idxStr === '' || $idxStr === null) {
            return 'Falta definir la respuesta correcta (gabarito) para esta pregunta en el banco.';
        }

        $idx = is_numeric($idxStr) ? (int) $idxStr : -1;
        if ($idx < 0 || $idx >= count($opts)) {
            return 'Hay un problema con el marcado de la respuesta correcta en el banco; revisá esta pregunta con el equipo editorial.';
        }

        $letter = chr(ord('A') + $idx);
        $text = trim((string) ($opts[$idx] ?? ''));
        if ($text === '') {
            return "Según el banco, la opción marcada como correcta es {$letter}.";
        }

        $snippet = mb_strlen($text) > 380 ? mb_substr($text, 0, 377).'…' : $text;

        $notaRaw = isset($data['nota']) ? mb_strtolower((string) $data['nota']) : '';
        $asksIncorrect = str_contains($notaRaw, 'incorreta') || str_contains($notaRaw, 'incorrecta');
        $asksCorrect = str_contains($notaRaw, 'correta') || str_contains($notaRaw, 'correcta');

        if ($asksIncorrect) {
            return 'En esta consigna debías señalar la opción INCORRECTA. '
                ."Esa es {$letter}: {$snippet} "
                .'Las demás alternativas son válidas respecto del tema planteado; '
                .'revisá el enunciado y contrastá conceptos con tu bibliografía.';
        }

        if ($asksCorrect) {
            return 'La opción CORRECTA es '.$letter.': '.$snippet.' '
                .'Las otras opciones no satisfacen de forma adecuada lo pedido en la pregunta.';
        }

        return 'Según el banco de preguntas, la respuesta esperada es la opción '.$letter.': '.$snippet;
    }

    public function isCorrect(?string $answer): bool
    {
        if ($answer === null || $answer === '') {
            return false;
        }

        return $this->getCorrectAnswer() === (string) $answer;
    }

    public function getPergunta(): string
    {
        $t = $this->data['pergunta']
            ?? $this->data['enunciado']
            ?? $this->data['texto']
            ?? $this->data['questao']
            ?? '';

        return is_string($t) && trim($t) !== '' ? trim($t) : 'Questão sem título';
    }

    /**
     * Alternativas na ordem exibida (índice 0 = A, etc.).
     *
     * @return list<string>
     */
    public function getOpcoes(): array
    {
        $raw = $this->data['opcoes'] ?? $this->data['alternativas'] ?? $this->data['opciones'] ?? null;
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        $first = reset($raw);
        if (is_array($first) && (isset($first['texto']) || isset($first['text']))) {
            $out = [];
            foreach ($raw as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $out[] = (string) ($item['texto'] ?? $item['text'] ?? '');
            }

            return $out;
        }

        $numericKeys = array_keys($raw) === range(0, count($raw) - 1);
        if (! $numericKeys) {
            $order = ['A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e'];
            $ordered = [];
            foreach ($order as $letter) {
                if (array_key_exists($letter, $raw)) {
                    $ordered[] = (string) $raw[$letter];
                }
            }
            if ($ordered !== []) {
                return $ordered;
            }
        }

        return array_values(array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $raw));
    }

    public function getData(): array
    {
        return $this->data;
    }
}
