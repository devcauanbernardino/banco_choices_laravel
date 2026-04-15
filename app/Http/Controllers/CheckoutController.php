<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $materias = $request->session()->get('signup_materias', []);
        $planId = $request->session()->get('signup_plan', '');

        if (empty($materias) || $planId === '') {
            return redirect()->route('signup.materias');
        }

        $materiasInfo = Materia::whereIn('id', $materias)->get();
        $plan = config("signup.plans.{$planId}");

        if (!$plan) {
            return redirect()->route('signup.plano');
        }

        $totalPrice = $plan['price'] * count($materias);
        $orderId = 'ORDER-' . strtoupper(uniqid());

        $request->session()->put('checkout_order_id', $orderId);

        return view('checkout.show', compact(
            'materiasInfo', 'plan', 'planId', 'totalPrice', 'orderId', 'materias'
        ));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'order_id' => 'required|string',
            'plan_id' => 'required|in:monthly,semester,annual',
            'plan_duration_days' => 'required|integer|min:1',
            'materias' => 'required|string',
        ]);

        $plan = config("signup.plans.{$request->input('plan_id')}");
        if (!$plan) {
            return redirect()->route('checkout.show')->with('error', 'Plano inválido.');
        }

        $materiasIds = array_filter(array_map('intval', explode(',', $request->input('materias'))));
        $totalPrice = $plan['price'] * count($materiasIds);

        $accessToken = config('mercadopago.access_token');
        if (empty($accessToken)) {
            return redirect()->route('checkout.show')->with('error', 'MP não configurado.');
        }

        // Create order record
        Pedido::create([
            'email' => $request->input('email'),
            'nome' => $request->input('name'),
            'valor_total' => $totalPrice,
            'status' => 'awaiting_payment',
            'stripe_payment_id' => $request->input('order_id'),
        ]);

        MercadoPagoConfig::setAccessToken($accessToken);

        $nomesMaterias = Materia::whereIn('id', $materiasIds)->pluck('nome')->toArray();
        $itemTitle = 'Banco de Choices — ' . (count($nomesMaterias) ? implode(', ', $nomesMaterias) : 'Materias');

        $siteUrl = config('mercadopago.site_url');

        $preferenceData = [
            'items' => [[
                'title' => $itemTitle,
                'quantity' => 1,
                'unit_price' => $totalPrice,
                'currency_id' => config('mercadopago.currency_id'),
            ]],
            'payer' => [
                'email' => $request->input('email'),
                'name' => $request->input('name'),
            ],
            'back_urls' => [
                'success' => $siteUrl . '/payment-success?status=approved&order_id=' . rawurlencode($request->input('order_id')),
                'failure' => $siteUrl . '/checkout-mercadopago?error=payment_failed',
                'pending' => $siteUrl . '/payment-success?status=pending&order_id=' . rawurlencode($request->input('order_id')),
            ],
            'auto_return' => 'approved',
            'notification_url' => $siteUrl . '/webhook-mercadopago',
            'external_reference' => $request->input('order_id'),
            'metadata' => [
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'plan_id' => $request->input('plan_id'),
                'plan_duration_days' => (string) $request->input('plan_duration_days'),
                'materias' => implode(',', $materiasIds),
            ],
        ];

        try {
            $client = new PreferenceClient();
            $preference = $client->create($preferenceData);
        } catch (\Throwable $e) {
            Log::error('MP preference error: ' . $e->getMessage());
            return redirect()->route('checkout.show')->with('error', 'Erro ao criar preferência.');
        }

        $initPoint = $preference->init_point ?: ($preference->sandbox_init_point ?? null);
        if (!$initPoint) {
            return redirect()->route('checkout.show')->with('error', 'Resposta inválida do MP.');
        }

        $request->session()->put('pending_order', [
            'order_id' => $request->input('order_id'),
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'total_price' => $totalPrice,
            'plan_id' => $request->input('plan_id'),
        ]);

        return redirect()->away($initPoint);
    }

    public function success(Request $request)
    {
        $status = $request->query('status', '');
        $orderId = $request->query('order_id', '');
        $pendingOrder = $request->session()->get('pending_order', []);

        return view('checkout.success', compact('status', 'orderId', 'pendingOrder'));
    }
}
