<?php

namespace Tests\Feature;

use App\Models\DemoAttempt;
use App\Models\Materia;
use App\Models\Pedido;
use App\Models\Referral;
use App\Models\User;
use App\Services\Referral\ReferralService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Coverage for checklist items in docs/PROMPT_CURSOR.md (referral + demo guards + fulfillment credit).
 */
class PromptCursorComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_validar_codigo_invalido(): void
    {
        $r = ReferralService::validarCodigo('BC-DOESNT', 'buyer@example.test', null);
        self::assertFalse($r['ok']);
        self::assertSame('invalid', $r['msg']);
    }

    public function test_referral_autoreferral_bloqueado_por_email(): void
    {
        User::query()->create([
            'nome' => 'Alice',
            'email' => 'alice@example.test',
            'senha' => Hash::make('secret'),
            'codigo_cupom' => 'BC-A11111',
            'saldo_credito' => 0,
        ]);

        $v = ReferralService::validarCodigo('BC-A11111', 'alice@example.test', null);
        self::assertFalse($v['ok']);
        self::assertSame('self_email', $v['msg']);
    }

    public function test_referral_autoreferral_bloqueado_por_user_id_autenticado(): void
    {
        User::query()->create([
            'nome' => 'Bob',
            'email' => 'bob@example.test',
            'senha' => Hash::make('secret'),
            'codigo_cupom' => 'BC-B22222',
            'saldo_credito' => 0,
        ]);

        /** @var User $bob */
        $bob = User::query()->where('email', 'bob@example.test')->first();

        $v = ReferralService::validarCodigo('BC-B22222', 'buyer-distinto@example.test', (int) $bob->id);
        self::assertFalse($v['ok']);
        self::assertSame('self', $v['msg']);
    }

    public function test_demo_iniciar_redirecciona_paywall_cinco_tentativas_24h(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $mat = Materia::query()->create(['nome' => 'Micro']);

        $uuid = 'aaaaaaaa-bbbb-4ccc-8123-111111111111';
        foreach (range(1, 5) as $_) {
            DemoAttempt::query()->create([
                'session_uuid' => $uuid,
                'ip' => '203.0.113.42',
                'materia_id' => $mat->id,
                'questao_id' => null,
                'acertou' => true,
            ]);
        }

        $this->withCookie('bc_demo_session', $uuid)->post(route('demo.iniciar'), [
            'materia_id' => $mat->id,
        ])
            ->assertRedirect(route('demo.paywall'));

        $paywallResp = $this->get(route('demo.paywall'));
        $paywallResp->assertOk();
    }

    public function test_fulfillment_credita_referrer_por_pedido_completed_idempotente(): void
    {
        $referrer = User::query()->create([
            'nome' => 'Ref',
            'email' => 'ref@example.test',
            'senha' => Hash::make('secret'),
            'codigo_cupom' => 'BC-R33333',
            'saldo_credito' => 0,
        ]);

        $pedido = Pedido::query()->create([
            'email' => 'buyer@example.test',
            'nome' => 'Buyer',
            'valor_total' => '1000.00',
            'status' => 'completed',
            'stripe_payment_id' => 'ORDER-UNIT-TEST-1',
            'codigo_cupom_usado' => 'BC-R33333',
        ]);

        ReferralService::processarFulfillmentPorPedidoId((int) $pedido->id);

        self::assertTrue(Referral::query()->where('pedido_id', $pedido->id)->exists());

        /** @var User $referrerFresh */
        $referrerFresh = User::query()->find($referrer->id);
        self::assertEqualsWithDelta(100.0, (float) ($referrerFresh->saldo_credito ?? 0), 0.01); // 10% * 1000

        ReferralService::processarFulfillmentPorPedidoId((int) $pedido->id); // segunda vez

        self::assertSame(1, (int) Referral::query()->where('pedido_id', $pedido->id)->count());
        $referrerTwice = User::query()->find($referrer->id);
        self::assertEqualsWithDelta((float) ($referrerFresh->saldo_credito ?? 0), (float) ($referrerTwice->saldo_credito ?? 0), 0.01);
    }
}
