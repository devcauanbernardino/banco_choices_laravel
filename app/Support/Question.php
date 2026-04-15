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
        return $this->data['feedback'] ?? 'Sem explicação disponível';
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
