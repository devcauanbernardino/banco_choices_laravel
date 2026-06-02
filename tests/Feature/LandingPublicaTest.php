<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('BANCO DE PREGUNTAS', false);
    }

    public function test_home_includes_modalidades_anchor(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="modalidades"', false);
        $response->assertSee('id="planes"', false);
        $response->assertSee('id="faq"', false);
    }

    public function test_favicon_route_serves_png(): void
    {
        $response = $this->get('/favicon.ico');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function test_home_includes_favicon_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/favicon.ico', false);
    }

    public function test_demo_show_renders(): void
    {
        $response = $this->get('/probar-gratis');

        $response->assertOk();
    }

    public function test_demo_configurar_renders(): void
    {
        $response = $this->get('/probar-gratis/configurar');

        $response->assertOk();
        $response->assertSee('Generador', false);
    }
}
