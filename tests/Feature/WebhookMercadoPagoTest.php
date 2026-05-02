<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebhookMercadoPagoTest extends TestCase
{
    public function test_webhook_rejects_when_signature_required_and_secret_empty(): void
    {
        config(['mercadopago.require_webhook_signature' => true]);
        config(['mercadopago.webhook_secret' => '']);

        $response = $this->postJson('/webhook-mercadopago', []);

        $response->assertStatus(503);
        $response->assertJsonFragment(['status' => 'webhook_secret_required']);
    }
}
