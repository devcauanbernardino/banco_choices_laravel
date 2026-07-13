<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\QuestionBankLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulationRepeatSameFilterTest extends TestCase
{
    use RefreshDatabase;

    private const MATERIA_ID_TESTE = 999993;

    private ?string $stubPath = null;

    protected function tearDown(): void
    {
        if ($this->stubPath !== null && File::exists($this->stubPath)) {
            File::delete($this->stubPath);
        }

        parent::tearDown();
    }

    public function test_pagina_de_resultado_tem_botao_para_repetir_com_o_mesmo_filtro(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Repetir Filtro',
        ]);

        $catedraId = DB::table('catedras')->insertGetId([
            'materia_id' => self::MATERIA_ID_TESTE,
            'nome' => 'Cátedra I',
            'slug' => 'catedra-i',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stubPath = storage_path('app/data/'.QuestionBankLocator::filenameFor(self::MATERIA_ID_TESTE));
        File::ensureDirectoryExists(dirname($this->stubPath));

        $quests = [];
        for ($n = 0; $n < 3; $n++) {
            $quests[] = [
                'pergunta' => 'Pergunta de teste '.$n,
                'opcoes' => ['Opção A', 'Opção B'],
                'gabarito' => 'A',
                'feedback' => 'Feedback '.$n,
            ];

            DB::table('questoes')->insert([
                'materia_id' => self::MATERIA_ID_TESTE,
                'catedra_id' => $catedraId,
                'overlay_key' => $n,
                'parcial' => 'primer_parcial',
                'tema' => 'Tema X',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        File::put($this->stubPath, json_encode(['questoes' => $quests], JSON_UNESCAPED_UNICODE));

        $user = User::query()->create([
            'nome' => 'Tester Repetir',
            'email' => 'repetir-filtro-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        $this->actingAs($user);

        $this->post(route('simulation.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'catedra_id' => $catedraId,
            'parcial' => ['primer_parcial'],
            'tema' => ['Tema X'],
            'quantidade' => 3,
            'modo' => 'estudo',
        ])->assertRedirect(route('simulation.show'));

        // Responde e avança pelas 3 questões até o resultado.
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('simulation.process'), ['resposta' => '0', 'avancar' => 1]);
        }

        $html = $this->get(route('result.show'))->assertOk()->getContent();

        $this->assertStringContainsString('name="materia" value="'.self::MATERIA_ID_TESTE.'"', $html);
        $this->assertStringContainsString('name="catedra_id" value="'.$catedraId.'"', $html);
        $this->assertStringContainsString('name="parcial[]" value="primer_parcial"', $html);
        $this->assertStringContainsString('name="tema[]" value="Tema X"', $html);
        $this->assertStringContainsString('name="quantidade" value="3"', $html);
        $this->assertStringContainsString('name="modo" value="estudo"', $html);

        // Repete com o mesmo filtro (mesmos campos que o form da view envia).
        $this->post(route('simulation.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'catedra_id' => $catedraId,
            'parcial' => ['primer_parcial'],
            'tema' => ['Tema X'],
            'quantidade' => 3,
            'modo' => 'estudo',
        ])->assertRedirect(route('simulation.show'));

        $this->get(route('simulation.show'))
            ->assertOk()
            ->assertSee('Pergunta de teste', false);
    }
}
