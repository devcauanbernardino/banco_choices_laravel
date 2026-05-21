<?php

namespace Tests\Unit;

use App\Services\Questions\QuestionTemaSuggester;
use PHPUnit\Framework\TestCase;

class QuestionTemaSuggesterTest extends TestCase
{
    public function test_sugere_tema_quando_enunciado_contem_palavra_chave(): void
    {
        $taxonomy = [
            'Egito antigo' => ['egito', 'nilo'],
            'Feudalismo' => ['feudal'],
        ];
        $blob = [
            'pergunta' => 'Sobre o Egito Antigo: qual função política do faraó?',
            'opcoes' => ['A', 'B'],
            'gabarito' => 'A',
        ];
        $r = QuestionTemaSuggester::suggest($blob, $taxonomy);
        $this->assertSame('Egito antigo', $r['tema']);
        $this->assertGreaterThan(0, $r['score']);
        $this->assertContains('egito', $r['hits']);
    }

    public function test_retorna_null_sem_match(): void
    {
        $taxonomy = ['Tema X' => ['aaaaxyz']];
        $blob = ['pergunta' => 'Texto sem relação', 'opcoes' => ['1'], 'gabarito' => 'A'];
        $r = QuestionTemaSuggester::suggest($blob, $taxonomy);
        $this->assertNull($r['tema']);
        $this->assertSame(0.0, $r['score']);
    }
}
