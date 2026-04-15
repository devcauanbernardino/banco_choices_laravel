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
        return $this->data['resposta_correta'] ?? '';
    }

    public function getFeedback(): string
    {
        return $this->data['feedback'] ?? 'Sem explicação disponível';
    }

    public function isCorrect(?string $answer): bool
    {
        return $answer !== null && $this->getCorrectAnswer() === $answer;
    }

    public function getPergunta(): string
    {
        return $this->data['pergunta'] ?? $this->data['texto'] ?? 'Questão sem título';
    }

    public function getData(): array
    {
        return $this->data;
    }
}
