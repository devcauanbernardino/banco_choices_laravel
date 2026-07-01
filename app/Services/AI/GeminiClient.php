<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClient
{
    public function generate(string $prompt, ?string $systemInstruction = null): string
    {
        return $this->call([
            ['role' => 'user', 'parts' => [['text' => $prompt]]],
        ], $systemInstruction);
    }

    /**
     * @param  list<array{role: string, texto: string}>  $turns  histórico em ordem cronológica (role: 'user'|'model')
     */
    public function chat(array $turns, ?string $systemInstruction = null): string
    {
        $contents = array_map(fn (array $t) => [
            'role' => $t['role'] === 'user' ? 'user' : 'model',
            'parts' => [['text' => $t['texto']]],
        ], $turns);

        return $this->call($contents, $systemInstruction);
    }

    /**
     * @param  list<array{role: string, parts: list<array{text: string}>}>  $contents
     */
    private function call(array $contents, ?string $systemInstruction = null): string
    {
        $key = (string) config('services.gemini.key');
        $model = (string) config('services.gemini.model', 'gemini-2.5-flash');

        if ($key === '') {
            throw new RuntimeException('GEMINI_API_KEY não configurada.');
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1536,
                // gemini-2.5-flash gasta tokens de "pensamento" antes da resposta visível;
                // desligar evita que respostas simples sejam cortadas antes de terminar.
                'thinkingConfig' => ['thinkingBudget' => 0],
            ],
        ];

        if ($systemInstruction !== null && $systemInstruction !== '') {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemInstruction]],
            ];
        }

        $response = Http::timeout(25)
            ->withHeaders(['x-goog-api-key' => $key])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", $payload);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao consultar IA (Gemini): '.$response->status().' '.$response->body());
        }

        $finishReason = data_get($response->json(), 'candidates.0.finishReason');
        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('IA não retornou conteúdo (finishReason: '.($finishReason ?? 'desconhecido').').');
        }

        if ($finishReason === 'MAX_TOKENS') {
            throw new RuntimeException('Resposta da IA foi cortada por limite de tokens.');
        }

        return trim($text);
    }
}
