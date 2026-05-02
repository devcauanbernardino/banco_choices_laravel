<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulationExamTimerTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_mode_questionnaire_renders_timer_markup_and_script(): void
    {
        $stubPath = storage_path('app/data/questoes_microbiologia_refinado.json');
        File::ensureDirectoryExists(dirname($stubPath));
        $quests = [];
        for ($n = 1; $n <= 6; $n++) {
            $quests[] = [
                'pergunta' => 'Pergunta de teste '.$n,
                'opcoes' => ['Primera opción '.$n, 'Segunda opción '.$n],
                'gabarito' => 'A',
                'feedback' => 'Comentário editorial suficientemente longo para teste '.$n.'.',
            ];
        }
        File::put($stubPath, json_encode(['questoes' => $quests], JSON_UNESCAPED_UNICODE));

        Materia::query()->create(['nome' => 'Microbiologia']);

        $user = User::query()->create([
            'nome' => 'Tester',
            'email' => 'timer-test@example.test',
            'senha' => Hash::make('secret'),
        ]);
        $user->materias()->attach(1);

        $this->actingAs($user);

        $this->post(route('simulation.create'), [
            'materia' => 1,
            'quantidade' => 3,
            'modo' => 'exame',
        ])->assertRedirect(route('simulation.show'));

        $html = $this->get(route('simulation.show'))->assertOk()->getContent();

        $this->assertStringContainsString('id="timerDisplay"', $html);
        $this->assertStringContainsString('formatRemaining', $html);
        $this->assertStringContainsString('setInterval', $html);

        $this->assertNotNull(session('simulado.inicio'));
        $this->assertSame('exame', session('simulado.modo'));
    }
}
