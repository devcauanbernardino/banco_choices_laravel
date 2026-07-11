<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardEvolucaoGraficoTest extends TestCase
{
    use RefreshDatabase;

    private const MATERIA_ID_TESTE = 999997;

    public function test_grafico_mostra_os_dez_dias_mais_recentes_em_ordem_crescente(): void
    {
        DB::table('materias')->insert([
            'id' => self::MATERIA_ID_TESTE,
            'nome' => 'Materia Teste Evolucao',
        ]);

        $user = User::query()->create([
            'nome' => 'Tester Evolucao',
            'email' => 'evolucao-test@example.test',
            'senha' => Hash::make('secret'),
            'mascote' => 'robo',
        ]);

        // 15 dias distintos de histórico (01/06 a 15/06) — mais que o limite de 10 do gráfico.
        for ($dia = 1; $dia <= 15; $dia++) {
            DB::table('historico_simulados')->insert([
                'usuario_id' => $user->id,
                'materia_id' => self::MATERIA_ID_TESTE,
                'acertos' => 7,
                'total_questoes' => 10,
                'data_realizacao' => sprintf('2026-06-%02d 10:00:00', $dia),
            ]);
        }

        $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

        preg_match_all('/class="chart-label"[^>]*>([^<]*)</', $html, $matches);
        $labelsDoGrafico = $matches[1];

        // Deve mostrar exatamente os 10 dias mais recentes (06/06 a 15/06), em ordem crescente.
        $esperado = array_map(fn (int $dia) => sprintf('%02d/06', $dia), range(6, 15));
        $this->assertSame($esperado, $labelsDoGrafico);
    }
}
