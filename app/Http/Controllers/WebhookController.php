<?php

namespace App\Http\Controllers;

use App\Services\MercadoPago\PaymentFulfillmentService;
use App\Services\MercadoPago\WebhookSignatureValidator;
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
        $secret = config('mercadopago.webhook_secret', '');

        if ($secret !== '') {
            $valid = WebhookSignatureValidator::validate(
                $rawBody,
                $request->server->all(),
                $request->query->all(),
                $secret
            );

            if (!$valid) {
                Log::warning('MP webhook signature invalid');
                return response()->json(['status' => 'signature_invalid'], 401);
            }
        }

        $data = json_decode($rawBody, true);
        $type = $data['type'] ?? ($request->query('type') ?? '');
        $dataId = $data['data']['id'] ?? ($request->query('data_id') ?? $request->query('id'));

        if ($type !== 'payment' || !$dataId) {
            return response()->json(['status' => 'ignored']);
        }

        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
        $paymentClient = new PaymentClient();

        try {
            $payment = $paymentClient->get((int) $dataId);
        } catch (\Throwable $e) {
            Log::error('MP payment get error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }

        $result = PaymentFulfillmentService::processPaymentNotification(
            DB::connection()->getPdo(),
            $payment
        );

        return response()->json($result);
    }
}
