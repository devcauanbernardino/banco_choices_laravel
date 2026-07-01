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
                'maxOutputTokens' => 700,
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

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('IA não retornou conteúdo.');
        }

        return trim($text);
    }
}
