<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────────
Route::get('/', [PageController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
// GET aceito para favoritos / links antigos; formulários continuam usando POST + @csrf
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/esqueci-senha', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/esqueci-senha', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/redefinir-senha/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/redefinir-senha', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/cadastro', [RegisterController::class, 'register'])->name('register');
Route::match(['get', 'post'], '/set-locale', [LocaleController::class, 'setLocale'])->name('set-locale');

// ── Signup flow (public) ────────────────────────────────────
Route::get('/selecionar-materias', [SignupController::class, 'selecionarMaterias'])->name('signup.materias');
Route::post('/selecionar-materias', [SignupController::class, 'storeMaterias']);
Route::get('/selecionar-plano', [SignupController::class, 'selecionarPlano'])->name('signup.plano');
Route::post('/selecionar-plano', [SignupController::class, 'storePlano']);

// ── Checkout / Payment ──────────────────────────────────────
Route::get('/checkout-mercadopago', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/process-payment-mp', [CheckoutController::class, 'processPayment'])->name('checkout.process');
Route::post('/process-payment-addon', [CheckoutController::class, 'processPayment'])->name('checkout.process.addon');
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
});
