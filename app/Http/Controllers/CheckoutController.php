<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Pedido;
use App\Services\MercadoPago\PaymentFulfillmentService;
use App\Services\Referral\ReferralService;
use App\Support\CheckoutDraftSession;
use App\Support\CheckoutErrorMessages;
use App\Support\Countries;
use App\Support\PaidAccessGrant;
use App\Support\SignupFlow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use MercadoPago\Client\Payment\PaymentClient;
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
            'codigo_cupom_usado' => 'nullable|string|max:64',
            'usar_credito_addon' => 'nullable|accepted',
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

        $baseTotal = (float) $plan['price'] * count($materiasIds);
        $cupRaw = trim((string) $request->input('codigo_cupom_usado', ''));
        $totalPagar = $baseTotal;
        $normalizedCupom = null;
        if ($cupRaw !== '') {
            $v = ReferralService::validarCodigo($cupRaw, trim((string) $request->input('email')), null);
            if (! $v['ok']) {
                return redirect()->route('checkout.show')->with('error', __('referral.checkout_codigo_invalido'));
            }
            $normalizedCupom = strtoupper(trim($cupRaw));
            $totalPagar = ReferralService::aplicarDescontoSubtotal($baseTotal);
        }

        $accessToken = config('mercadopago.access_token');
        if (empty($accessToken)) {
            return redirect()->route('checkout.show')->with('error', CheckoutErrorMessages::mercadopagoNotConfigured());
        }

        Pedido::create([
            'email' => $request->input('email'),
            'nome' => $request->input('name'),
            'valor_total' => round($totalPagar, 2),
            'status' => 'awaiting_payment',
            'stripe_payment_id' => $request->input('order_id'),
            'codigo_cupom_usado' => $normalizedCupom,
        ]);

        $redirect = $this->createMercadoPagoPreference(
            $request,
            $materiasIds,
            $totalPagar,
            'signup',
            $request->input('email'),
            $request->input('name'),
            0.0,
            $normalizedCupom ?? '',
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

        /** @var \App\Models\User $userFresh */
        $userFresh = $user->fresh() ?? $user;

        $materiasIds = array_values(array_filter(array_map('intval', explode(',', $request->input('materias')))));
        $baseTotal = SignupFlow::addonPricePerMateria() * count($materiasIds);

        $cupRaw = trim((string) $request->input('codigo_cupom_usado', ''));
        $totalDepoisCupom = $baseTotal;
        $normalizedCupom = null;
        if ($cupRaw !== '') {
            $v = ReferralService::validarCodigo($cupRaw, trim((string) $userFresh->email), (int) $userFresh->id);
            if (! $v['ok']) {
                return redirect()->route('addon.checkout')->with('error', __('referral.checkout_codigo_invalido'));
            }
            $normalizedCupom = strtoupper(trim($cupRaw));
            $totalDepoisCupom = ReferralService::aplicarDescontoSubtotal($baseTotal);
        }

        $credMax = round(max(0, (float) $userFresh->saldo_credito), 2);
        $usarCred = $request->boolean('usar_credito_addon') && $credMax > 0;
        $credUtil = $usarCred ? round(min($credMax, $totalDepoisCupom), 2) : 0.0;

        $valorMp = round(max(0, $totalDepoisCupom - $credUtil), 2);

        $planRow = SignupFlow::signupPlanForDisplayById((string) $request->input('plan_id'));
        $planDaysAddon = $planRow !== null ? (int) ($planRow['durationDays'] ?? 0) : 0;
        if ($planDaysAddon <= 0) {
            return redirect()->route('addon.checkout')->with('error', CheckoutErrorMessages::planNotFound());
        }

        if ($valorMp <= 0.02) {
            PaidAccessGrant::addonComCreditoOuGratuito(
                $userFresh,
                round($totalDepoisCupom, 2),
                (string) $request->input('plan_id'),
                $planDaysAddon,
                $materiasIds,
                $request->input('order_id'),
                round($credUtil, 2),
                $normalizedCupom,
            );

            CheckoutDraftSession::clearAddonDraft();

            return redirect()->route('dashboard')->with('success', __('referral.checkout_gratuito_ok'));
        }

        $accessToken = config('mercadopago.access_token');
        if (empty($accessToken)) {
            return redirect()->route('addon.checkout')->with('error', CheckoutErrorMessages::mercadopagoNotConfigured());
        }

        Pedido::create([
            'email' => $userFresh->email,
            'nome' => $userFresh->nome,
            'valor_total' => round($totalDepoisCupom, 2),
            'status' => 'awaiting_payment',
            'stripe_payment_id' => $request->input('order_id'),
            'codigo_cupom_usado' => $normalizedCupom,
        ]);

        $redirect = $this->createMercadoPagoPreference(
            $request,
            $materiasIds,
            $valorMp,
            'addon',
            $userFresh->email,
            $userFresh->nome,
            $credUtil,
            $normalizedCupom ?? '',
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
        string $name,
        float $addonCreditoReservadoMeta = 0.0,
        string $cupomCodigoParaMeta = '',
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
                'success' => $pBase.'/payment-success',
                'failure' => $failureUrl,
                'pending' => $pBase.'/payment-success',
            ],
            'external_reference' => $request->input('order_id'),
            'metadata' => [
                'email' => $email,
                'name' => $name,
                'plan_id' => $request->input('plan_id'),
                'plan_duration_days' => (string) $request->input('plan_duration_days'),
                'materias' => implode(',', $materiasIds),
                'codigo_cupom_usado' => strtoupper(trim($cupomCodigoParaMeta)),
                'valor_credito_utilizado_addon' => ($checkoutKind === 'addon' && $addonCreditoReservadoMeta > 0.005)
                    ? (string) round($addonCreditoReservadoMeta, 2)
                    : '0',
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
            'plan_duration_days' => (int) $request->input('plan_duration_days'),
            'materias_csv' => implode(',', $materiasIds),
            'checkout_kind' => $checkoutKind,
        ]);

        return redirect()->away($initPoint);
    }

    public function success(Request $request)
    {
        $paymentIdRaw = $request->query('payment_id') ?: $request->query('collection_id');
        $orderId = (string) ($request->query('order_id') ?: $request->query('external_reference', ''));
        $pendingOrder = $request->session()->get('pending_order', []);

        if ($orderId === '' && ! empty($pendingOrder['order_id'])) {
            $orderId = (string) $pendingOrder['order_id'];
        }

        $queryStatus = strtolower((string) ($request->query('status') ?: $request->query('collection_status', '')));
        $displayStatus = $this->normalizeMercadoPagoStatusForView($queryStatus);

        if (! $paymentIdRaw && $orderId !== '' && config('mercadopago.access_token')) {
            $paymentIdRaw = $this->searchMercadoPagoPaymentIdByExternalReference($orderId);
        }

        $metaFallback = $this->buildMetaFallbackFromPendingOrder($pendingOrder);

        $syncedFromMercadoPago = false;
        if ($paymentIdRaw && config('mercadopago.access_token')) {
            MercadoPagoConfig::setAccessToken((string) config('mercadopago.access_token'));
            try {
                $payment = (new PaymentClient)->get((int) $paymentIdRaw);
                PaymentFulfillmentService::processPaymentNotification(
                    DB::connection()->getPdo(),
                    $payment,
                    $metaFallback
                );
                $syncedFromMercadoPago = true;
                $apiStatus = strtolower((string) ($payment->status ?? ''));
                if ($apiStatus !== '') {
                    $displayStatus = $this->normalizeMercadoPagoStatusForView($apiStatus) ?: $displayStatus;
                }
            } catch (\Throwable $e) {
                Log::warning('MP payment sync on success page failed', [
                    'payment_id' => $paymentIdRaw,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($displayStatus === '' && $queryStatus !== '') {
            $displayStatus = $this->normalizeMercadoPagoStatusForView($queryStatus);
        }

        if ($displayStatus === '') {
            $displayStatus = 'unknown';
        }

        $status = $displayStatus;

        return view('checkout.success', compact('status', 'orderId', 'pendingOrder', 'syncedFromMercadoPago'));
    }

    /**
     * Completa plano/matérias quando a API do MP não devolve metadata no Payment (comum em sandbox / carteira).
     *
     * @param  array<string, mixed>  $pending
     * @return array<string, string>
     */
    private function buildMetaFallbackFromPendingOrder(array $pending): array
    {
        if ($pending === []) {
            return [];
        }

        $orderId = trim((string) ($pending['order_id'] ?? ''));
        $planId = strtolower(trim((string) ($pending['plan_id'] ?? '')));
        $email = trim((string) ($pending['email'] ?? ''));
        $name = trim((string) ($pending['name'] ?? ''));
        $materiasCsv = trim((string) ($pending['materias_csv'] ?? ''));

        if ($planId === '' || $materiasCsv === '') {
            return [];
        }

        $plan = SignupFlow::signupPlanForDisplayById($planId);
        $days = $plan !== null
            ? (int) $plan['durationDays']
            : (int) ($pending['plan_duration_days'] ?? 0);

        if ($days <= 0) {
            return [];
        }

        $out = [
            'plan_id' => $planId,
            'plan_duration_days' => (string) $days,
            'materias' => $materiasCsv,
        ];

        if ($email !== '') {
            $out['email'] = $email;
        }
        if ($name !== '') {
            $out['name'] = $name;
        }
        if ($orderId !== '') {
            $out['external_reference'] = $orderId;
        }

        return $out;
    }

    /**
     * Quando o retorno do MP não traz payment_id na query, tenta localizar o pagamento pelo external_reference (ORDER-…).
     */
    private function searchMercadoPagoPaymentIdByExternalReference(string $externalReference): ?string
    {
        $token = (string) config('mercadopago.access_token');
        if ($token === '' || $externalReference === '') {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(20)
                ->acceptJson()
                ->get('https://api.mercadopago.com/v1/payments/search', [
                    'external_reference' => $externalReference,
                    'sort' => 'date_created',
                    'criteria' => 'desc',
                    'limit' => 20,
                ]);

            if (! $response->successful()) {
                Log::warning('MP payments/search failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            $results = $response->json('results', []);
            if (! is_array($results)) {
                return null;
            }

            foreach ($results as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (($row['status'] ?? '') === 'approved' && isset($row['id'])) {
                    return (string) (int) $row['id'];
                }
            }

            if (isset($results[0]['id'])) {
                return (string) (int) $results[0]['id'];
            }
        } catch (\Throwable $e) {
            Log::warning('MP payments/search exception', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function normalizeMercadoPagoStatusForView(string $status): string
    {
        $s = strtolower(trim($status));
        if ($s === 'approved') {
            return 'approved';
        }
        if (in_array($s, ['pending', 'in_process', 'authorized'], true)) {
            return 'pending';
        }
        if (in_array($s, ['rejected', 'cancelled', 'refunded', 'charged_back', 'in_mediation'], true)) {
            return 'failed';
        }

        return '';
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
