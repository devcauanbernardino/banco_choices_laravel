<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\QuestionBankLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulationPerAlternativeExplanationTest extends TestCase
{
    use RefreshDatabase;

    private const MATERIA_ID_TESTE = 999995;

    private ?string $stubPath = null;

    protected function tearDown(): void
    {
        if ($this->stubPath !== null && File::exists($this->stubPath)) {
            File::delete($this->stubPath);
        }

        parent::tearDown();
    }

    public function test_mostra_explicacao_por_alternativa_e_nada_onde_falta_conteudo(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Explicacoes',
        ]);

        $this->stubPath = storage_path('app/data/'.QuestionBankLocator::filenameFor(self::MATERIA_ID_TESTE));
        File::ensureDirectoryExists(dirname($this->stubPath));

        File::put($this->stubPath, json_encode(['questoes' => [[
            'pergunta' => 'Pergunta de teste com explicações por alternativa',
            'opcoes' => ['Opção errada com texto', 'Opção correta', 'Opção errada sem texto'],
            'gabarito' => 'B',
            'feedback' => 'Feedback antigo (fallback)',
            'explicacoes' => [
                'Explicação de por que A está errada.',
                'Explicação de por que B é a correta.',
                '',
            ],
        ]]], JSON_UNESCAPED_UNICODE));

        $user = User::query()->create([
            'nome' => 'Tester Explicacoes',
            'email' => 'explicacoes-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        $this->actingAs($user);

        $this->post(route('simulation.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'quantidade' => 1,
            'modo' => 'estudo',
        ])->assertRedirect(route('simulation.show'));

        $this->post(route('simulation.process'), [
            'resposta' => '0',
            'resposta_confirm' => '1',
        ])->assertRedirect(route('simulation.show'));

        $html = $this->get(route('simulation.show'))->assertOk()->getContent();

        $this->assertStringContainsString('Explicação de por que A está errada.', $html);
        $this->assertStringContainsString('Explicação de por que B é a correta.', $html);
        $this->assertStringContainsString('qz-opt-explain--correct', $html);

        // A opção C não tem texto em "explicacoes" e não é a correta — não deve aparecer nenhuma caixa pra ela.
        $this->assertStringNotContainsString('Feedback antigo (fallback)', $html);
    }

    public function test_alternativa_correta_usa_feedback_antigo_quando_nao_ha_explicacao_nova(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Explicacoes Fallback',
        ]);

        $this->stubPath = storage_path('app/data/'.QuestionBankLocator::filenameFor(self::MATERIA_ID_TESTE));
        File::ensureDirectoryExists(dirname($this->stubPath));

        File::put($this->stubPath, json_encode(['questoes' => [[
            'pergunta' => 'Pergunta sem explicações migradas ainda',
            'opcoes' => ['Errada', 'Correta'],
            'gabarito' => 'B',
            'feedback' => 'Feedback único antigo ainda em uso.',
        ]]], JSON_UNESCAPED_UNICODE));

        $user = User::query()->create([
            'nome' => 'Tester Fallback',
            'email' => 'explicacoes-fallback-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        $this->actingAs($user);

        $this->post(route('simulation.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'quantidade' => 1,
            'modo' => 'estudo',
        ])->assertRedirect(route('simulation.show'));

        $this->post(route('simulation.process'), [
            'resposta' => '1',
            'resposta_confirm' => '1',
        ])->assertRedirect(route('simulation.show'));

        $html = $this->get(route('simulation.show'))->assertOk()->getContent();

        $this->assertStringContainsString('Feedback único antigo ainda em uso.', $html);
    }
}
