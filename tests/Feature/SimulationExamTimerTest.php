<?php

namespace Tests\Feature;

use App\Support\QuestionBankLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulationExamTimerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID fora do mapeamento hardcoded de QuestionBankLocator::filenameFor(), pra não
     * colidir com um banco de questões real (ex.: id=1 aponta pro banco de Microbiologia).
     */
    private const MATERIA_ID_TESTE = 999999;

    private ?string $stubPath = null;

    protected function tearDown(): void
    {
        if ($this->stubPath !== null && File::exists($this->stubPath)) {
            File::delete($this->stubPath);
        }

        parent::tearDown();
    }

    public function test_exam_mode_questionnaire_renders_timer_markup_and_script(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Microbiologia Teste',
        ]);

        $this->stubPath = storage_path('app/data/'.QuestionBankLocator::filenameFor(self::MATERIA_ID_TESTE));
        File::ensureDirectoryExists(dirname($this->stubPath));

        $quests = [];
        for ($n = 1; $n <= 6; $n++) {
            $quests[] = [
                'pergunta' => 'Pergunta de teste '.$n,
                'opcoes' => ['Primera opción '.$n, 'Segunda opción '.$n],
                'gabarito' => 'A',
                'feedback' => 'Comentário editorial suficientemente longo para teste '.$n.'.',
            ];
        }
        File::put($this->stubPath, json_encode(['questoes' => $quests], JSON_UNESCAPED_UNICODE));

        $user = \App\Models\User::query()->create([
            'nome' => 'Tester',
            'email' => 'timer-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        $this->actingAs($user);

        $this->post(route('simulation.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'quantidade' => 3,
            'modo' => 'exame',
        ])->assertRedirect(route('simulation.show'));

        $html = $this->get(route('simulation.show'))->assertOk()->getContent();

        $this->assertStringContainsString('id="timerDisplay"', $html);
        $this->assertStringContainsString('formatRemaining', $html);
        $this->assertStringContainsString('setInterval', $html);

        $row = DB::table('simulados_em_andamento')->where('usuario_id', $user->id)->first();
        $dados = json_decode((string) $row->dados, true);

        $this->assertNotNull($dados['inicio'] ?? null);
        $this->assertSame('exame', $dados['modo'] ?? null);
    }
}
