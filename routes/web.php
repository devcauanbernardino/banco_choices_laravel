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
    Route::post('/demo/iniciar', [DemoController::class, 'iniciar'])->name('demo.iniciar');
    Route::get('/demo/pregunta', [DemoController::class, 'questao'])->name('demo.questao');
    Route::post('/demo/responder', [DemoController::class, 'responder'])->name('demo.responder');
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
