<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\QuestionBankLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulationResumeAfterLogoutTest extends TestCase
{
    use RefreshDatabase;

    private const MATERIA_ID_TESTE = 999998;

    private ?string $stubPath = null;

    protected function tearDown(): void
    {
        if ($this->stubPath !== null && File::exists($this->stubPath)) {
            File::delete($this->stubPath);
        }

        parent::tearDown();
    }

    public function test_progresso_do_simulado_sobrevive_a_logout_e_login(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Resume',
        ]);

        $this->stubPath = storage_path('app/data/'.QuestionBankLocator::filenameFor(self::MATERIA_ID_TESTE));
        File::ensureDirectoryExists(dirname($this->stubPath));

        $quests = [];
        for ($n = 1; $n <= 5; $n++) {
            $quests[] = [
                'pergunta' => 'Pergunta de teste '.$n,
                'opcoes' => ['Primeira opção '.$n, 'Segunda opção '.$n],
                'gabarito' => 'A',
                'feedback' => 'Comentário editorial suficientemente longo para teste '.$n.'.',
            ];
        }
        File::put($this->stubPath, json_encode(['questoes' => $quests], JSON_UNESCAPED_UNICODE));

        $user = User::query()->create([
            'nome' => 'Tester Resume',
            'email' => 'resume-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        $this->actingAs($user);

        $this->post(route('simulation.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'quantidade' => 5,
            'modo' => 'estudo',
        ])->assertRedirect(route('simulation.show'));

        // Responde a primeira questão e avança para a segunda.
        $this->post(route('simulation.process'), ['resposta' => 'A'])
            ->assertRedirect(route('simulation.show'));
        $this->post(route('simulation.process'), ['avancar' => 1])
            ->assertRedirect(route('simulation.show'));

        // Confirma que o progresso já está persistido no banco (não só na sessão PHP).
        $row = DB::table('simulados_em_andamento')->where('usuario_id', $user->id)->first();
        $this->assertNotNull($row);
        $dados = json_decode((string) $row->dados, true);
        $this->assertSame(1, $dados['atual']);

        // O pack é embaralhado, então guardamos qual pergunta está no índice 1
        // (a que deve continuar aparecendo após retomar) para comparar depois.
        $perguntaEsperada = $dados['questoes'][1]['pergunta'];

        // Faz logout de verdade (invalida a sessão) e loga de novo — simula fechar o
        // navegador/trocar de dispositivo e voltar depois.
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->actingAs($user);

        $html = $this->get(route('simulation.show'))->assertOk()->getContent();

        // Deve mostrar a mesma pergunta de onde parou, não reiniciar do zero.
        $this->assertStringContainsString($perguntaEsperada, $html);
    }
}
