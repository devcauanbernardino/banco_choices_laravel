<?php

namespace App\Services\Flashcards;

use App\Models\FlashcardConteudo;
use App\Services\AI\GeminiClient;
use App\Support\Question;
use RuntimeException;

class FlashcardContentGenerator
{
    /**
     * Retorna o conteúdo de frente/verso do cartão pra essa questão+idioma, gerando via IA
     * e cacheando na primeira vez (compartilhado entre todos os usuários).
     */
    public static function getOrGenerate(int $questaoId, Question $questao, string $idioma): FlashcardConteudo
    {
        $existente = FlashcardConteudo::query()
            ->where('questao_id', $questaoId)
            ->where('idioma', $idioma)
            ->first();

        if ($existente !== null) {
            return $existente;
        }

        [$frente, $verso] = self::gerar($questao, $idioma);

        return FlashcardConteudo::create([
            'questao_id' => $questaoId,
            'idioma' => $idioma,
            'frente' => $frente,
            'verso' => $verso,
        ]);
    }

    /**
     * @return array{0: string, 1: string} [frente, verso]
     */
    private static function gerar(Question $questao, string $idioma): array
    {
        $nomeIdioma = match (substr($idioma, 0, 2)) {
            'es' => 'espanhol',
            'en' => 'inglês',
            default => 'português',
        };

        $system = 'Você transforma questões de múltipla escolha de medicina em flashcards de estudo (frente/verso), estilo Anki. '
            ."REGRA OBRIGATÓRIA E INEGOCIÁVEL: sua resposta (frente e verso) deve ser escrita 100% em {$nomeIdioma}, "
            .'independentemente do idioma do conteúdo de entrada que você receber. Nunca responda em outro idioma.';

        $opcoesTexto = implode("\n", $questao->getOpcoes());
        $prompt = "Questão de múltipla escolha (prova de medicina):\n{$questao->getPergunta()}\n\n"
            ."Alternativas:\n{$opcoesTexto}\n\n"
            .'Feedback/explicação: '.$questao->getFeedback()."\n\n"
            .'Transforme isso em UM flashcard de estudo simples (estilo Anki): uma "frente" objetiva '
            .'(pergunta curta ou termo-chave, sem listar alternativas) e um "verso" com a resposta direta e concisa '
            .'(pode incluir uma explicação breve de 1-2 frases). '
            ."Lembrete final: responda em {$nomeIdioma}, mesmo que o conteúdo acima esteja em outro idioma. "
            .'Responda SOMENTE com um JSON válido no formato {"frente": "...", "verso": "..."}, sem markdown, sem texto extra.';

        $resposta = app(GeminiClient::class)->generate($prompt, $system);
        $json = self::extrairJson($resposta);

        $frente = trim((string) ($json['frente'] ?? ''));
        $verso = trim((string) ($json['verso'] ?? ''));

        if ($frente === '' || $verso === '') {
            throw new RuntimeException('IA não retornou frente/verso válidos para o flashcard.');
        }

        return [$frente, $verso];
    }

    private static function extrairJson(string $raw): array
    {
        $texto = trim($raw);
        $texto = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $texto) ?? $texto;

        $decoded = json_decode(trim($texto), true);

        return is_array($decoded) ? $decoded : [];
    }
}
