<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Pedido;
use App\Support\CheckoutDraftSession;
use App\Support\CheckoutErrorMessages;
use App\Support\Countries;
use App\Support\SignupFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
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

        $materiasInfo = Materia::whereIn('id', $materias)->orderBy('nome')->get();
        $planDisplay = SignupFlow::signupPlanForDisplayById($planId);

        if ($planDisplay === null) {
            return redirect()->route('signup.plano');
        }

        $totalPrice = (float) $planDisplay['price'] * count($materias);
        $orderId = 'ORDER-'.strtoupper(uniqid());

        $request->session()->put('checkout_order_id', $orderId);

        CheckoutDraftSession::saveSignupDraft(
            $orderId,
            $planId,
            (int) $planDisplay['durationDays'],
            (float) $planDisplay['price'],
            $materias,
            $totalPrice
        );

        return view('checkout.show', [
            'materiasInfo' => $materiasInfo,
            'plan' => $planDisplay,
            'planId' => $planId,
            'totalPrice' => $totalPrice,
            'orderId' => $orderId,
            'materias' => $materias,
            'countries' => Countries::forSelect(),
        ]);
    }

    public function processPayment(Request $request)
    {
        $checkoutKind = $request->input('checkout_kind', 'signup');

        $request->validate([
            'checkout_kind' => 'nullable|in:signup,addon',
            'order_id' => 'required|string',
            'plan_id' => 'required|in:monthly,semester,annual',
            'plan_duration_days' => 'required|integer|min:1',
            'materias' => 'required|string',
            'total_price' => 'required|numeric|min:0',
            'terms' => 'accepted',
        ]);

        if ($checkoutKind === 'addon') {
            return $this->processAddonPayment($request);
        }

        return $this->processSignupPayment($request);
    }

    private function processSignupPayment(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'country' => ['required', 'string', 'size:2', Rule::in(Countries::validAlpha2Codes())],
            'postal_code' => 'nullable|string|max:32',
        ]);

        $draftCheck = CheckoutDraftSession::validateSignupPost($request);
        if ($draftCheck['ok'] !== true) {
            $reason = (string) ($draftCheck['reason'] ?? 'unknown');
            Log::warning('checkout_signup_draft_invalid', ['reason' => $reason]);

            return redirect()->route('checkout.show')->with('error', CheckoutErrorMessages::forDraftReason($reason));
        }

        $plan = SignupFlow::signupPlanForDisplayById($request->input('plan_id'));
        if ($plan === null) {
            return redirect()->route('checkout.show')->with('error', CheckoutErrorMessages::planNotFound());
        }

        $materiasIds = array_values(array_filter(array_map('intval', explode(',', $request->input('materias')))));
        $totalPrice = (float) $plan['price'] * count($materiasIds);

        $accessToken = config('mercadopago.access_token');
        if (empty($accessToken)) {
            return redirect()->route('checkout.show')->with('error', CheckoutErrorMessages::mercadopagoNotConfigured());
        }

        Pedido::create([
            'email' => $request->input('email'),
            'nome' => $request->input('name'),
            'valor_total' => $totalPrice,
            'status' => 'awaiting_payment',
            'stripe_payment_id' => $request->input('order_id'),
        ]);

        $redirect = $this->createMercadoPagoPreference(
            $request,
            $materiasIds,
            $totalPrice,
            'signup',
            $request->input('email'),
            $request->input('name')
        );

        if ($redirect === null) {
            return redirect()->route('checkout.show')->with('error', CheckoutErrorMessages::mercadopagoPreferenceFailed());
        }

        CheckoutDraftSession::clearSignupDraft();

        return $redirect;
    }

    private function processAddonPayment(Request $request)
    {
        $user = Auth::user();
        if ($user === null) {
            return redirect()->route('login')->with('error', __('addon.login_required'));
        }

        $draftCheck = CheckoutDraftSession::validateAddonPost($request, (int) $user->id);
        if ($draftCheck['ok'] !== true) {
            $reason = (string) ($draftCheck['reason'] ?? 'unknown');
            Log::warning('checkout_addon_draft_invalid', ['reason' => $reason]);

            return redirect()->route('addon.checkout')->with('error', CheckoutErrorMessages::forDraftReason($reason));
        }

        $request->validate([
            'country' => ['required', 'string', 'size:2', Rule::in(Countries::validAlpha2Codes())],
            'postal_code' => 'required|string|max:32',
        ]);

        $materiasIds = array_values(array_filter(array_map('intval', explode(',', $request->input('materias')))));
        $totalPrice = SignupFlow::addonPricePerMateria() * count($materiasIds);

        $accessToken = config('mercadopago.access_token');
        if (empty($accessToken)) {
            return redirect()->route('addon.checkout')->with('error', CheckoutErrorMessages::mercadopagoNotConfigured());
        }

        Pedido::create([
            'email' => $user->email,
            'nome' => $user->nome,
            'valor_total' => $totalPrice,
            'status' => 'awaiting_payment',
            'stripe_payment_id' => $request->input('order_id'),
        ]);

        $redirect = $this->createMercadoPagoPreference(
            $request,
            $materiasIds,
            $totalPrice,
            'addon',
            $user->email,
            $user->nome
        );

        if ($redirect === null) {
            return redirect()->route('addon.checkout')->with('error', CheckoutErrorMessages::mercadopagoPreferenceFailed());
        }

        CheckoutDraftSession::clearAddonDraft();

        return $redirect;
    }

    /**
     * @param  list<int>  $materiasIds
     */
    private function createMercadoPagoPreference(
        Request $request,
        array $materiasIds,
        float $totalPrice,
        string $checkoutKind,
        string $email,
        string $name
    ): ?RedirectResponse {
        $accessToken = config('mercadopago.access_token');
        MercadoPagoConfig::setAccessToken($accessToken);

        $nomesMaterias = Materia::whereIn('id', $materiasIds)->pluck('nome')->toArray();
        $itemTitle = 'Banco de Choices — '.(count($nomesMaterias) ? implode(', ', $nomesMaterias) : 'Materias');

        $pBase = $this->mercadopagoPreferenceBaseUrl();
        $failureUrl = $checkoutKind === 'addon'
            ? $pBase.'/checkout-addon?error=payment_failed'
            : $pBase.'/checkout-mercadopago?error=payment_failed';

        $unitPrice = round($totalPrice, 2);

        $preferenceData = [
            'items' => [[
                'title' => Str::limit($itemTitle, 240, ''),
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'currency_id' => config('mercadopago.currency_id'),
            ]],
            'payer' => [
                'email' => $email,
                'name' => $name,
            ],
            'back_urls' => [
                'success' => $pBase.'/payment-success?status=approved&order_id='.rawurlencode($request->input('order_id')),
                'failure' => $failureUrl,
                'pending' => $pBase.'/payment-success?status=pending&order_id='.rawurlencode($request->input('order_id')),
            ],
            'external_reference' => $request->input('order_id'),
            'metadata' => [
                'email' => $email,
                'name' => $name,
                'plan_id' => $request->input('plan_id'),
                'plan_duration_days' => (string) $request->input('plan_duration_days'),
                'materias' => implode(',', $materiasIds),
            ],
        ];

        if ($this->mercadopagoNotificationUrlAllowed($pBase)) {
            $preferenceData['notification_url'] = $pBase.'/webhook-mercadopago';
        }

        // auto_return exige back_urls em HTTPS (API rejeita http://localhost com invalid_auto_return).
        if ($this->mercadopagoAutoReturnAllowed($pBase)) {
            $preferenceData['auto_return'] = 'approved';
        }

        try {
            $client = new PreferenceClient;
            $preference = $client->create($preferenceData);
        } catch (MPApiException $e) {
            // Corpo da API (cause, message) — o SDK só expõe mensagem genérica em getMessage()
            Log::error('MP preference API error', [
                'status' => $e->getStatusCode(),
                'body' => $e->getApiResponse()->getContent(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('MP preference error: '.$e->getMessage(), ['exception' => $e]);

            return null;
        }

        $initPoint = $preference->init_point ?: ($preference->sandbox_init_point ?? null);
        if (! $initPoint) {
            return null;
        }

        $request->session()->put('pending_order', [
            'order_id' => $request->input('order_id'),
            'email' => $email,
            'name' => $name,
            'total_price' => $totalPrice,
            'plan_id' => $request->input('plan_id'),
            'checkout_kind' => $checkoutKind,
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

    /**
     * Base usada nas URLs da preferência (back_urls, webhook). Pode ser HTTPS (ngrok) enquanto SITE_URL é local.
     */
    private function mercadopagoPreferenceBaseUrl(): string
    {
        $explicit = config('mercadopago.checkout_base_url');
        if (is_string($explicit) && trim($explicit) !== '') {
            return rtrim(trim($explicit), '/');
        }

        return rtrim((string) config('mercadopago.site_url'), '/');
    }

    /**
     * O Mercado Pago costuma rejeitar notification_url com host local (localhost / 127.0.0.1).
     * Em produção, use SITE_URL público com HTTPS.
     */
    private function mercadopagoNotificationUrlAllowed(string $siteUrl): bool
    {
        $host = parse_url($siteUrl, PHP_URL_HOST);
        if ($host === '' || $host === false || $host === null) {
            return false;
        }

        $h = strtolower((string) $host);

        return $h !== 'localhost'
            && $h !== '127.0.0.1'
            && $h !== '[::1]'
            && ! str_ends_with($h, '.local');
    }

    /**
     * Mercado Pago rejeita auto_return quando back_urls.success não é HTTPS (ex.: http://localhost).
     */
    private function mercadopagoAutoReturnAllowed(string $siteUrl): bool
    {
        $scheme = parse_url($siteUrl, PHP_URL_SCHEME);

        return strtolower((string) $scheme) === 'https';
    }
}
