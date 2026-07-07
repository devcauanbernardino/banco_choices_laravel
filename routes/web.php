<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Api\CatalogoAjaxController;
use App\Http\Controllers\Api\CatalogoPublicController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ComunidadeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\FlashcardController;
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
Route::get('/termos-e-condicoes', [PageController::class, 'terms'])->name('terms');
Route::get('/verificar-pagamento', [\App\Http\Controllers\PaymentStatusController::class, 'show'])
    ->middleware('throttle:20,1')
    ->name('payment.status');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/esqueci-senha', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/esqueci-senha', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password-email')->name('password.email');
    Route::get('/redefinir-senha/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/redefinir-senha', [NewPasswordController::class, 'store'])->name('password.update');
});

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

// ── Legacy .php (app PHP antigo) → URLs Laravel (favoritos / links antigos) ──
Route::redirect('/bancoperguntas.php', '/bancoperguntas', 301);
Route::redirect('/simulados.php', '/simulados', 301);
Route::redirect('/estatisticas.php', '/estatisticas', 301);
Route::redirect('/comprar-materias.php', '/comprar-materias', 301);

Route::middleware('auth')->group(function () {
    Route::get('/trocar-senha-obrigatorio', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/trocar-senha-obrigatorio', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])->name('password.force-change.store');
    Route::get('/escolher-mascote', [\App\Http\Controllers\Auth\MascoteController::class, 'show'])->name('mascote.choose');
    Route::post('/escolher-mascote', [\App\Http\Controllers\Auth\MascoteController::class, 'store'])->name('mascote.store');
});

// ── Authenticated ───────────────────────────────────────────
Route::middleware(['auth', 'force.password.change', 'force.mascote.choice'])->group(function () {
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
    Route::post('/questionario/explicar-ia', [SimulationController::class, 'explainWithAi'])
        ->middleware('throttle:20,1')
        ->name('simulation.explainAi');

    Route::prefix('api/ia-chat')->group(function () {
        Route::get('/historico', [AiChatController::class, 'history'])->name('ai.chat.history');
        Route::post('/enviar', [AiChatController::class, 'send'])->middleware('throttle:20,1')->name('ai.chat.send');
        Route::post('/limpar', [AiChatController::class, 'clear'])->name('ai.chat.clear');
    });
    Route::get('/resultado/historico/{historico}', [ResultController::class, 'showHistory'])->name('simulation.result');
    Route::get('/resultado', [ResultController::class, 'show'])->name('result.show');

    Route::get('/flashcards', [FlashcardController::class, 'index'])->name('flashcards.index');
    Route::post('/flashcards/iniciar', [FlashcardController::class, 'create'])->name('flashcards.create');
    Route::post('/flashcards/revisar', [FlashcardController::class, 'process'])->name('flashcards.process');

    Route::get('/decks', [DeckController::class, 'index'])->name('decks.index');
    Route::post('/decks', [DeckController::class, 'store'])->name('decks.store');
    Route::post('/decks/importar-anki', [DeckController::class, 'storeAnki'])
        ->middleware('throttle:10,1')
        ->name('decks.import.anki');
    Route::get('/decks/descobrir', [DeckController::class, 'descobrir'])->name('decks.descobrir');
    Route::post('/decks/revisar/iniciar', [DeckController::class, 'create'])->name('decks.revisar.iniciar');
    Route::post('/decks/revisar', [DeckController::class, 'process'])->name('decks.revisar.process');

    Route::get('/pomodoro', [PomodoroController::class, 'index'])->name('pomodoro.index');
    Route::post('/pomodoro/ciclo', [PomodoroController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('pomodoro.ciclo.store');
    Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
    Route::put('/decks/{deck}', [DeckController::class, 'update'])->name('decks.update');
    Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->name('decks.destroy');
    Route::post('/decks/{deck}/compartilhar', [DeckController::class, 'share'])->name('decks.share');
    Route::post('/decks/{deck}/descompartilhar', [DeckController::class, 'unshare'])->name('decks.unshare');
    Route::post('/decks/{deck}/clonar', [DeckController::class, 'clonar'])->name('decks.clonar');
    Route::post('/decks/{deck}/cartas', [DeckController::class, 'storeCarta'])->name('decks.cartas.store');
    Route::put('/decks/{deck}/cartas/{carta}', [DeckController::class, 'updateCarta'])->name('decks.cartas.update');
    Route::delete('/decks/{deck}/cartas/{carta}', [DeckController::class, 'destroyCarta'])->name('decks.cartas.destroy');

    Route::get('/simulados', [HistoryController::class, 'index'])->name('history');
    Route::get('/estatisticas', [StatsController::class, 'index'])->name('stats');
    Route::get('/estatisticas/relatorio.pdf', [StatsController::class, 'exportPdf'])->name('stats.export-pdf');

    Route::get('/comunidade', [ComunidadeController::class, 'index'])->name('comunidade.index');
    Route::post('/comunidade', [ComunidadeController::class, 'store'])->middleware('throttle:20,1')->name('comunidade.store');
    Route::put('/comunidade/{post}', [ComunidadeController::class, 'update'])->middleware('throttle:20,1')->name('comunidade.update');
    Route::delete('/comunidade/{post}', [ComunidadeController::class, 'destroy'])->name('comunidade.destroy');
    Route::post('/comunidade/{post}/curtir', [ComunidadeController::class, 'toggleCurtidaPost'])->middleware('throttle:60,1')->name('comunidade.curtir');
    Route::post('/comunidade/{post}/comentarios', [ComunidadeController::class, 'comentar'])->middleware('throttle:30,1')->name('comunidade.comentar');
    Route::put('/comunidade/{post}/comentarios/{comentario}', [ComunidadeController::class, 'updateComentario'])->middleware('throttle:30,1')->name('comunidade.comentarios.update');
    Route::delete('/comunidade/{post}/comentarios/{comentario}', [ComunidadeController::class, 'destroyComentario'])->name('comunidade.comentarios.destroy');
    Route::post('/comunidade/{post}/comentarios/{comentario}/curtir', [ComunidadeController::class, 'toggleCurtidaComentario'])->middleware('throttle:60,1')->name('comunidade.comentarios.curtir');
    Route::post('/comunidade/{post}/denunciar', [ComunidadeController::class, 'denunciarPost'])->middleware('throttle:20,1')->name('comunidade.denunciar');
    Route::post('/comunidade/{post}/comentarios/{comentario}/denunciar', [ComunidadeController::class, 'denunciarComentario'])->middleware('throttle:20,1')->name('comunidade.comentarios.denunciar');

    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/referidos', [ReferralController::class, 'show'])->name('referral.show');
});
