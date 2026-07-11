<?php

namespace Tests\Unit;

use App\Support\QuestionLocale;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class QuestionLocaleExplicacoesOverlayTest extends TestCase
{
    private const BANK = 'questoes_overlay_test.json';

    protected function tearDown(): void
    {
        QuestionLocale::clearCache();
        $path = storage_path('app/data/i18n/pt_BR/'.self::BANK);
        if (File::exists($path)) {
            File::delete($path);
        }
        parent::tearDown();
    }

    public function test_overlay_substitui_explicacoes_por_mascote(): void
    {
        $path = storage_path('app/data/i18n/pt_BR/'.self::BANK);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            '0' => [
                'pergunta' => 'Pergunta traduzida',
                'opcoes' => ['Opção A traduzida', 'Opção B traduzida'],
                'explicacoes' => [
                    'robo' => ['Explicação lógica traduzida A', 'Explicação lógica traduzida B'],
                    'fantasma' => ['Explicação leve traduzida A', 'Explicação leve traduzida B'],
                    'gato' => ['Explicação curiosa traduzida A', 'Explicação curiosa traduzida B'],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $questao = [
            '_overlay_key' => 0,
            'numero' => 1,
            'pergunta' => 'Pregunta original',
            'opcoes' => [
                ['letra' => 'A', 'texto' => 'Opción A original'],
                ['letra' => 'B', 'texto' => 'Opción B original'],
            ],
            'explicacoes' => [
                'robo' => ['Explicación lógica A', 'Explicación lógica B'],
                'fantasma' => ['Explicación leve A', 'Explicación leve B'],
                'gato' => ['Explicación curiosa A', 'Explicación curiosa B'],
            ],
        ];

        $out = QuestionLocale::apply($questao, 'pt_BR', self::BANK);

        $this->assertSame('Pergunta traduzida', $out['pergunta']);
        $this->assertSame('Opção A traduzida', $out['opcoes'][0]['texto']);
        $this->assertSame(
            ['robo' => ['Explicação lógica traduzida A', 'Explicação lógica traduzida B'], 'fantasma' => ['Explicação leve traduzida A', 'Explicação leve traduzida B'], 'gato' => ['Explicação curiosa traduzida A', 'Explicação curiosa traduzida B']],
            $out['explicacoes']
        );
    }

    public function test_sem_overlay_de_explicacoes_mantem_original(): void
    {
        QuestionLocale::clearCache();
        $questao = [
            '_overlay_key' => 0,
            'numero' => 1,
            'pergunta' => 'Pregunta original',
            'opcoes' => [['letra' => 'A', 'texto' => 'Opción A']],
            'explicacoes' => ['robo' => ['Explicación A']],
        ];

        $out = QuestionLocale::apply($questao, 'pt_BR', 'banco_inexistente.json');

        $this->assertSame(['robo' => ['Explicación A']], $out['explicacoes']);
    }
}
