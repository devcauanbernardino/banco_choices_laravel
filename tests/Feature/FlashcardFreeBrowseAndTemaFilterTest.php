<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\FlashcardBankLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FlashcardFreeBrowseAndTemaFilterTest extends TestCase
{
    use RefreshDatabase;

    private const MATERIA_ID_TESTE = 999994;

    private ?string $stubPath = null;

    protected function tearDown(): void
    {
        if ($this->stubPath !== null && File::exists($this->stubPath)) {
            File::delete($this->stubPath);
        }

        parent::tearDown();
    }

    private function criarMateriaComBaralho(): User
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Flashcards',
            'slug' => 'materia-teste-flashcards-livre',
        ]);

        $this->stubPath = storage_path('app/data/'.FlashcardBankLocator::filenameFor(self::MATERIA_ID_TESTE));
        File::ensureDirectoryExists(dirname($this->stubPath));

        $cartas = [
            ['numero' => 1, 'frente' => 'Frente 1', 'verso' => 'Verso 1', 'tema' => 'Tema A'],
            ['numero' => 2, 'frente' => 'Frente 2', 'verso' => 'Verso 2', 'tema' => 'Tema A'],
            ['numero' => 3, 'frente' => 'Frente 3', 'verso' => 'Verso 3', 'tema' => 'Tema B'],
            ['numero' => 4, 'frente' => 'Frente 4', 'verso' => 'Verso 4'],
        ];
        File::put($this->stubPath, json_encode(['flashcards' => $cartas], JSON_UNESCAPED_UNICODE));

        $user = User::query()->create([
            'nome' => 'Tester Flashcards',
            'email' => 'flashcards-livre-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        return $user;
    }

    public function test_modo_livre_percorre_todos_os_cartoes_sem_gravar_progresso(): void
    {
        $user = $this->criarMateriaComBaralho();
        $this->actingAs($user);

        $res = $this->postJson(route('flashcards.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'modo' => 'livre',
        ])->assertOk()->json();

        $this->assertSame(4, $res['total']);
        $this->assertSame('livre', $res['modo']);

        $this->postJson(route('flashcards.process'), ['revelar' => 1])->assertOk();

        // Avaliar não é permitido no modo livre.
        $this->postJson(route('flashcards.process'), ['avaliar' => 'facil'])
            ->assertStatus(422);

        $this->assertDatabaseCount('flashcard_progresso', 0);
    }

    public function test_filtro_de_tema_restringe_a_fila_no_modo_livre(): void
    {
        $user = $this->criarMateriaComBaralho();
        $this->actingAs($user);

        $res = $this->postJson(route('flashcards.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'modo' => 'livre',
            'temas' => ['Tema A'],
        ])->assertOk()->json();

        $this->assertSame(2, $res['total']);
    }

    public function test_filtro_de_tema_restringe_a_fila_no_modo_revisao(): void
    {
        $user = $this->criarMateriaComBaralho();
        $this->actingAs($user);

        $res = $this->postJson(route('flashcards.create'), [
            'materia' => self::MATERIA_ID_TESTE,
            'modo' => 'revisao',
            'novos_por_dia' => 200,
            'temas' => ['Tema B'],
        ])->assertOk()->json();

        $this->assertSame(1, $res['total']);
    }

    public function test_pagina_de_flashcards_renderiza_com_faixa_de_resumo_e_filtro_de_tema(): void
    {
        $user = $this->criarMateriaComBaralho();
        $this->actingAs($user);

        $this->get(route('flashcards.index'))
            ->assertOk()
            ->assertSee('Tema A')
            ->assertSee('Tema B');
    }
}
