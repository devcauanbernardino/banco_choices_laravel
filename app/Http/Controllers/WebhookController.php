<?php

namespace App\Http\Controllers;

use App\Services\MercadoPago\PaymentFulfillmentService;
use App\Services\MercadoPago\WebhookSignatureValidator;
use App\Support\MercadoPagoAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        $rawBody = $request->getContent();
        $accounts = MercadoPagoAccount::all();

        $matchedAccount = $this->matchWebhookSignature($request, $rawBody, $accounts);
        if ($matchedAccount === false) {
            return response()->json(['status' => 'signature_invalid'], 401);
        }
        if ($matchedAccount === null) {
            return response()->json(['status' => 'webhook_secret_required'], 503);
        }

        $data = json_decode($rawBody, true);
        $type = $data['type'] ?? ($request->query('type') ?? '');
        $dataId = $data['data']['id'] ?? ($request->query('data_id') ?? $request->query('id'));

        if ($type !== 'payment' || ! $dataId) {
            return response()->json(['status' => 'ignored']);
        }

        // matchedAccount já identificou a conta (assinatura), mas se não havia secret configurado
        // (matchedAccount === true) tentamos cada conta até uma conseguir buscar o pagamento.
        $candidateAccounts = is_array($matchedAccount) ? [$matchedAccount] : array_values($accounts);

        $payment = null;
        $accessTokenUsed = '';
        foreach ($candidateAccounts as $account) {
            $token = (string) ($account['access_token'] ?? '');
            if ($token === '') {
                continue;
            }

            MercadoPagoConfig::setAccessToken($token);
            $paymentClient = new PaymentClient;

            try {
                $payment = $paymentClient->get((int) $dataId);
                $accessTokenUsed = $token;
                break;
            } catch (\Throwable $e) {
                Log::warning('MP payment get error (tentando próxima conta se houver): '.$e->getMessage());
            }
        }

        if ($payment === null) {
            Log::error('MP payment get error: nenhuma conta conseguiu buscar o pagamento', ['data_id' => $dataId]);

            return response()->json(['status' => 'error'], 500);
        }

        $metaFallback = PaymentFulfillmentService::fetchMetadataViaApi((int) ($payment->id ?? 0), $accessTokenUsed);

        $result = PaymentFulfillmentService::processPaymentNotification(
            DB::connection()->getPdo(),
            $payment,
            $metaFallback
        );

        return response()->json($result);
    }

    /**
     * Valida a assinatura do webhook contra cada conta configurada.
     *
     * @param  array<string, array{webhook_secret: string}>  $accounts
     * @return array{webhook_secret: string}|true|false|null true = nenhum secret configurado (segue sem identificar conta),
     *                                                        array = conta cuja assinatura bateu, false = assinatura inválida
     *                                                        em todas, null = secret exigido mas nenhum configurado
     */
    private function matchWebhookSignature(Request $request, string $rawBody, array $accounts)
    {
        $secretsConfigured = array_filter(array_map(
            fn (array $a) => trim((string) ($a['webhook_secret'] ?? '')),
            $accounts
        ));

        if (empty($secretsConfigured)) {
            if (config('mercadopago.require_webhook_signature')) {
                Log::warning('Mercado Pago webhook rejected: nenhum MP_WEBHOOK_SECRET configurado enquanto assinatura é exigida');

                return null;
            }

            return true;
        }

        foreach ($accounts as $account) {
            $secret = trim((string) ($account['webhook_secret'] ?? ''));
            if ($secret === '') {
                continue;
            }

            $valid = WebhookSignatureValidator::validate(
                $rawBody,
                $request->server->all(),
                $request->query->all(),
                $secret
            );

            if ($valid) {
                return $account;
            }
        }

        Log::warning('MP webhook signature invalid em todas as contas configuradas');

        return false;
    }
}
