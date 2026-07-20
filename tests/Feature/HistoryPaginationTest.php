<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HistoryPaginationTest extends TestCase
{
    use RefreshDatabase;

    private const MATERIA_ID_TESTE = 999994;

    public function test_pagina_de_simulados_pagina_o_historico_em_paginas_de_15(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Paginacao',
        ]);

        $user = User::query()->create([
            'nome' => 'Tester Paginacao',
            'email' => 'paginacao-historico-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);
        $user->materias()->attach(self::MATERIA_ID_TESTE);

        for ($i = 0; $i < 18; $i++) {
            DB::table('historico_simulados')->insert([
                'usuario_id' => $user->id,
                'materia_id' => self::MATERIA_ID_TESTE,
                'acertos' => 5,
                'total_questoes' => 10,
                'detalhes_json' => json_encode(['v' => 1, 'modo' => 'estudo', 'detalhes' => []]),
                'data_realizacao' => now()->subMinutes($i),
            ]);
        }

        $this->actingAs($user);

        $pageOne = $this->get(route('history'))->assertOk()->getContent();
        $this->assertSame(15, substr_count($pageOne, 'bc-mock-historico__subject-pill'));
        $this->assertStringContainsString('page=2', $pageOne);

        $pageTwo = $this->get(route('history', ['page' => 2]))->assertOk()->getContent();
        $this->assertSame(3, substr_count($pageTwo, 'bc-mock-historico__subject-pill'));
    }
}
