<?php

namespace Tests\Feature;

use App\Models\DemoAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_show_returns_200(): void
    {
        $this->get('/probar-gratis')->assertOk();
    }

    public function test_demo_configurar_with_facultad_query_returns_200(): void
    {
        // sem dados, ainda assim deve renderizar graciosamente
        $response = $this->get('/probar-gratis/configurar?faculdade=uba');
        $response->assertOk();
    }

    public function test_demo_resultado_route_renders(): void
    {
        $response = $this->get('/demo/resultado?acertos=3&total=5');
        $response->assertOk();
    }

    public function test_demo_paywall_alias_still_works(): void
    {
        $response = $this->get('/demo/paywall?acertos=0&total=5');
        $response->assertOk();
    }

    public function test_demo_attempt_table_accepts_user_agent_hash(): void
    {
        // Cria matéria mínima para satisfazer a FK
        $materiaId = (int) \DB::table('materias')->insertGetId([
            'nome' => 'TestMat',
            'slug' => 'test-mat',
            'ordem' => 1,
        ]);

        DemoAttempt::create([
            'session_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'ip' => '127.0.0.1',
            'user_agent_hash' => sha1('test-ua'),
            'materia_id' => $materiaId,
            'questao_id' => null,
            'acertou' => true,
        ]);

        $this->assertDatabaseHas('demo_attempts', [
            'user_agent_hash' => sha1('test-ua'),
        ]);
    }
}
