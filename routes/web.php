<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Api\CatalogoAjaxController;
use App\Http\Controllers\Api\CatalogoPublicController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\LandingCssController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────────
Route::get('/favicon.ico', FaviconController::class)
    ->name('favicon')
    ->withoutMiddleware([\App\Http\Middleware\SetLocale::class]);

Route::get('/css/landing-v2.css', LandingCssController::class)
    ->name('landing.css')
    ->withoutMiddleware([\App\Http\Middleware\SetLocale::class]);

Route::get('/', [PageController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/esqueci-senha', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/esqueci-senha', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password-email')->name('password.email');
    Route::get('/redefinir-senha/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/redefinir-senha', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/cadastro', [RegisterController::class, 'register'])->middleware('throttle:cadastro')->name('register');
Route::match(['get', 'post'], '/set-locale', [LocaleController::class, 'setLocale'])->name('set-locale');

// ── Catálogo público (signup / addons UI) ───────────────────
Route::middleware(['throttle:120,1'])->prefix('catalogo-publico')->group(function () {
    Route::get('/faculdades', [CatalogoPublicController::class, 'faculdades'])->name('catalogo.public.faculdades');
    Route::get('/agrupamentos', [CatalogoPublicController::class, 'agrupamentos'])->name('catalogo.public.agrupamentos');
    Route::get('/materias', [CatalogoPublicController::class, 'materias'])->name('catalogo.public.materias');
    Route::get('/catedras', [CatalogoPublicController::class, 'catedras'])->name('catalogo.public.catedras');
});

// ── Demo gratuito ─────────────────────────────────────────────
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/probar-gratis', [DemoController::class, 'show'])->name('demo.show');
    Route::get('/probar-gratis/configurar', [DemoController::class, 'configurar'])->name('demo.configurar');
    Route::post('/demo/iniciar', [DemoController::class, 'iniciar'])->name('demo.iniciar');
    Route::get('/demo/pregunta', [DemoController::class, 'questao'])->name('demo.questao');
    Route::post('/demo/responder', [DemoController::class, 'responder'])->name('demo.responder');
    Route::get('/demo/resultado', [DemoController::class, 'resultado'])->name('demo.resultado');
    // Alias retrocompatível: rotas antigas que apontavam para paywall continuam funcionando.
    Route::get('/demo/paywall', [DemoController::class, 'paywall'])->name('demo.paywall');
});

// ── Signup flow (public) ────────────────────────────────────
Route::get('/selecionar-materias', [SignupController::class, 'selecionarMaterias'])->name('signup.materias');
Route::post('/selecionar-materias', [SignupController::class, 'storeMaterias']);
Route::get('/selecionar-plano', [SignupController::class, 'selecionarPlano'])->name('signup.plano');
Route::post('/selecionar-plano', [SignupController::class, 'storePlano']);

// ── Checkout / Payment ──────────────────────────────────────
Route::get('/checkout-mercadopago', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/process-payment-mp', [CheckoutController::class, 'processPayment'])->middleware('throttle:checkout-payment')->name('checkout.process');
Route::post('/process-payment-addon', [CheckoutController::class, 'processPayment'])->middleware('throttle:checkout-payment')->name('checkout.process.addon');
Route::get('/payment-success', [CheckoutController::class, 'success'])->name('checkout.success');

// ── Webhook (no CSRF) ───────────────────────────────────────
Route::post('/webhook-mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhook.mp');

// ── DEBUG TEMPORÁRIO — remover após uso ──────────────────────
Route::get('/bc-debug-process', function () {
    if (request('token') !== 'bc2026debug') {
        abort(403);
    }

    $paymentId = (int) request('payment_id');
    if ($paymentId <= 0) {
        return response('payment_id obrigatório', 400);
    }

    \MercadoPago\MercadoPagoConfig::setAccessToken((string) config('mercadopago.access_token'));
    $payment = (new \MercadoPago\Client\Payment\PaymentClient)->get($paymentId);

    $result = \App\Services\MercadoPago\PaymentFulfillmentService::processPaymentNotification(
        \Illuminate\Support\Facades\DB::connection()->getPdo(),
        $payment
    );

    return response('<pre>'.htmlspecialchars(print_r($result, true)).'</pre>');
});

Route::get('/bc-debug-mail', function () {
    if (request('token') !== 'bc2026debug') {
        abort(403);
    }

    $config = [
        'APP_URL' => config('app.url'),
        'SITE_URL' => config('mercadopago.site_url'),
        'MP_CHECKOUT_BASE_URL' => config('mercadopago.checkout_base_url'),
        'MP_ACCESS_TOKEN_set' => config('mercadopago.access_token') !== '' ? 'sim' : 'NAO',
        'MP_REQUIRE_WEBHOOK_SIGNATURE' => config('mercadopago.require_webhook_signature') ? 'true' : 'false',
        'MP_WEBHOOK_SECRET_set' => config('mercadopago.webhook_secret') !== '' ? 'sim' : 'NAO',
        'MAIL_MAILER' => config('mail.default'),
        'MAIL_HOST' => config('mail.mailers.smtp.host'),
        'MAIL_PORT' => config('mail.mailers.smtp.port'),
        'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
        'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
        'MAIL_FROM_ADDRESS' => config('mail.from.address'),
        'MAIL_FROM_NAME' => config('mail.from.name'),
    ];

    $logTestResult = 'ok';
    try {
        \Illuminate\Support\Facades\Log::info('BC_DEBUG_TEST '.now()->toDateTimeString());
    } catch (\Throwable $e) {
        $logTestResult = 'EXCEPTION: '.$e->getMessage();
    }

    $directWriteResult = 'ok';
    try {
        $r = file_put_contents(storage_path('logs/laravel.log'), '['.now()->toDateTimeString()."] BC_DEBUG_DIRECT_WRITE\n", FILE_APPEND);
        $directWriteResult = $r === false ? 'FALSE (falhou)' : "ok ({$r} bytes)";
    } catch (\Throwable $e) {
        $directWriteResult = 'EXCEPTION: '.$e->getMessage();
    }

    $logPath = storage_path('logs/laravel.log');
    $logTail = '(arquivo não encontrado)';
    $info = '';
    if (file_exists($logPath)) {
        clearstatcache(true, $logPath);
        $info = "logPath={$logPath}\nsize=".filesize($logPath)." bytes\nmtime=".date('Y-m-d H:i:s', filemtime($logPath))."\nwritable=".(is_writable($logPath) ? 'sim' : 'NAO')."\nLog::info_result={$logTestResult}\ndirect_write_result={$directWriteResult}\n";
        $lines = file($logPath);
        $info .= 'total_lines='.count($lines)."\n";
        $filter = request('filter');
        if ($filter) {
            $lines = array_values(array_filter($lines, fn ($l) => stripos($l, $filter) !== false));
        }
        $logTail = implode('', array_slice($lines, -300));
    }

    $otherLogs = glob(storage_path('logs/*.log'));

    $pedidos = \Illuminate\Support\Facades\DB::table('pedidos')->orderByDesc('id')->limit(5)->get();
    $users = \Illuminate\Support\Facades\DB::table('users')->orderByDesc('id')->limit(5)->get(['id', 'nome', 'email', 'created_at']);
    $processed = \Illuminate\Support\Facades\DB::table('mp_payment_processed')->orderByDesc('mp_payment_id')->limit(5)->get();

    $mpSearch = '(nao consultado)';
    if (config('mercadopago.access_token')) {
        try {
            $resp = \Illuminate\Support\Facades\Http::withToken((string) config('mercadopago.access_token'))
                ->timeout(20)
                ->acceptJson()
                ->get('https://api.mercadopago.com/v1/payments/search', [
                    'sort' => 'date_created',
                    'criteria' => 'desc',
                    'limit' => 10,
                ]);
            $mpSearch = 'status='.$resp->status()."\n".print_r($resp->json(), true);
        } catch (\Throwable $e) {
            $mpSearch = 'EXCEPTION: '.$e->getMessage();
        }
    }

    $extra = "\nMP_PAYMENTS_SEARCH:\n".$mpSearch
        ."\nPEDIDOS:\n".print_r($pedidos->toArray(), true)
        ."\nUSERS:\n".print_r($users->toArray(), true)
        ."\nMP_PAYMENT_PROCESSED:\n".print_r($processed->toArray(), true);

    return response('<pre>'.htmlspecialchars(print_r($config, true))."\n".htmlspecialchars($info)."\nOUTROS LOGS: ".htmlspecialchars(print_r($otherLogs, true)).htmlspecialchars($extra)."\n\n--- ÚLTIMAS LINHAS DO LOG ---\n\n".htmlspecialchars($logTail).'</pre>');
});

// ── Legacy .php (app PHP antigo) → URLs Laravel (favoritos / links antigos) ──
Route::redirect('/bancoperguntas.php', '/bancoperguntas', 301);
Route::redirect('/simulados.php', '/simulados', 301);
Route::redirect('/estatisticas.php', '/estatisticas', 301);
Route::redirect('/comprar-materias.php', '/comprar-materias', 301);

// ── Authenticated ───────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::prefix('api/catalogo')->group(function () {
        Route::get('/agrupamentos', [CatalogoAjaxController::class, 'agrupamentos'])->name('api.catalogo.agrupamentos');
        Route::get('/faculdades', [CatalogoAjaxController::class, 'faculdades'])->name('api.catalogo.faculdades');
        Route::get('/materias', [CatalogoAjaxController::class, 'materias'])->name('api.catalogo.materias');
        Route::get('/catedras', [CatalogoAjaxController::class, 'catedras'])->name('api.catalogo.catedras');
        Route::get('/temas', [CatalogoAjaxController::class, 'temas'])->name('api.catalogo.temas');
        Route::get('/parciais', [CatalogoAjaxController::class, 'parciais'])->name('api.catalogo.parciais');
    });

    Route::match(['get', 'post'], '/comprar-materias', [AddonController::class, 'materias'])->name('addon.materias');
    Route::get('/comprar-plano', [AddonController::class, 'planoRedirect'])->name('addon.plano');
    Route::get('/checkout-addon', [AddonController::class, 'checkout'])->name('addon.checkout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/bancoperguntas', [QuestionBankController::class, 'index'])->name('questionbank');
    Route::post('/criar-simulado', [SimulationController::class, 'create'])->name('simulation.create');
    Route::get('/questionario', [SimulationController::class, 'show'])->name('simulation.show');
    Route::post('/processa', [SimulationController::class, 'process'])->name('simulation.process');
    Route::get('/resultado/historico/{historico}', [ResultController::class, 'showHistory'])->name('simulation.result');
    Route::get('/resultado', [ResultController::class, 'show'])->name('result.show');

    Route::get('/simulados', [HistoryController::class, 'index'])->name('history');
    Route::get('/estatisticas', [StatsController::class, 'index'])->name('stats');
    Route::get('/estatisticas/relatorio.pdf', [StatsController::class, 'exportPdf'])->name('stats.export-pdf');

    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/referidos', [ReferralController::class, 'show'])->name('referral.show');
});
