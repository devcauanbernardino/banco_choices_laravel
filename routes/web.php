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
Route::get('/bc-debug-sendmail', function () {
    if (request('token') !== 'bc2026debug') {
        abort(403);
    }

    $to = (string) request('to', 'devcauanbernardino@gmail.com');

    \Illuminate\Support\Facades\Artisan::call('config:clear');
    $host = config('mail.mailers.smtp.host');
    $username = config('mail.mailers.smtp.username');
    $password = (string) config('mail.mailers.smtp.password');

    if (request('mailq') === '1') {
        $output = @shell_exec('/usr/sbin/exim -bp 2>&1') ?: @shell_exec('exim -bp 2>&1') ?: 'exec indisponível';

        return response((string) $output);
    }

    if (request('rawsendmail') === '1') {
        $path = '/usr/sbin/sendmail';
        $out = 'exists='.(file_exists($path) ? 'yes' : 'no').' executable='.(is_executable($path) ? 'yes' : 'no')."\n\n";

        $msg = "From: contato@bancodechoices.com\r\nTo: {$to}\r\nSubject: Teste raw sendmail\r\n\r\nCorpo de teste raw sendmail.\r\n";

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($path.' -bs -i', $descriptors, $pipes);
        if (is_resource($proc)) {
            fwrite($pipes[0], "EHLO bancodechoices.com\r\n");
            fwrite($pipes[0], "MAIL FROM:<contato@bancodechoices.com>\r\n");
            fwrite($pipes[0], "RCPT TO:<{$to}>\r\n");
            fwrite($pipes[0], "DATA\r\n");
            fwrite($pipes[0], $msg."\r\n.\r\n");
            fwrite($pipes[0], "QUIT\r\n");
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exit = proc_close($proc);
            $out .= "STDOUT:\n{$stdout}\n\nSTDERR:\n{$stderr}\n\nEXIT: {$exit}\n";
        } else {
            $out .= "proc_open falhou\n";
        }

        return response($out);
    }

    if (request('rundeploy') === '1') {
        $cmd = '/bin/bash /home2/cauanb36/repositories/banco_choices_laravel/deploy/hostgator-auto-deploy.sh 2>&1';
        $output = @shell_exec($cmd) ?: '(sem saída)';

        return response($output);
    }

    if (request('contatobox') === '1') {
        $base = '/home2/cauanb36/mail/bancodechoices.com/contato';
        $out = '';
        foreach (['new', 'cur', '.Trash/new', '.Trash/cur', '.Junk/new', '.Junk/cur'] as $sub) {
            $d = "{$base}/{$sub}";
            $out .= "-- {$sub} --\n";
            if (! is_dir($d)) {
                $out .= "(não existe)\n";
                continue;
            }
            $files = array_values(array_diff(@scandir($d) ?: [], ['.', '..']));
            $out .= empty($files) ? "(vazio)\n" : implode("\n", $files)."\n";
        }

        return response($out);
    }

    if (request('mailtree') === '1') {
        $base = '/home2/cauanb36/mail';
        $out = '';
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        $count = 0;
        foreach ($it as $f) {
            if ($it->getDepth() > 4) {
                continue;
            }
            $out .= str_repeat('  ', $it->getDepth()).$f->getFilename().($f->isDir() ? '/' : '')."\n";
            $count++;
            if ($count > 300) {
                $out .= "... (truncado)\n";
                break;
            }
        }

        return response($out);
    }

    if (request('eximlog') === '1') {
        $candidates = [
            '/var/log/exim_mainlog',
            '/usr/local/cpanel/logs/exim_mainlog',
            '/home2/cauanb36/var/log/exim_mainlog',
        ];
        $out = '';
        foreach ($candidates as $f) {
            $out .= "== {$f} ==\n";
            if (is_readable($f)) {
                $lines = @file($f);
                $out .= $lines ? implode('', array_slice($lines, -40)) : '(vazio)';
            } else {
                $out .= "(sem acesso ou não existe)\n";
            }
            $out .= "\n\n";
        }

        $maildir = '/home2/cauanb36/mail/bancodechoices.com/contato';
        $out .= "== {$maildir} ==\n";
        if (is_dir($maildir)) {
            foreach (['new', 'cur'] as $sub) {
                $d = "{$maildir}/{$sub}";
                $out .= "-- {$sub} --\n";
                $files = @scandir($d) ?: [];
                $files = array_values(array_diff($files, ['.', '..']));
                rsort($files);
                $out .= implode("\n", array_slice($files, 0, 10))."\n";
            }
        } else {
            $out .= "(diretório não existe)\n";
        }

        return response($out);
    }

    if (request('track') === '1') {
        $cmds = [
            'uapi EmailTrack get_email_trace_summary domain=bancodechoices.com 2>&1',
            'uapi --output=jsonpretty EmailTrack get_email_trace_summary domain=bancodechoices.com 2>&1',
        ];
        $out = '';
        foreach ($cmds as $cmd) {
            $out .= "\$ {$cmd}\n";
            $out .= (string) (@shell_exec($cmd) ?: "(sem saída)\n");
            $out .= "\n\n";
        }

        return response($out);
    }

    if (request('checkcss') === '1') {
        $path = public_path('assets/css/landing-v2.css');
        $repoPath = base_path('public/assets/css/landing-v2.css');
        $log = '';
        if (is_file('/home2/cauanb36/deploy-auto.log')) {
            $log = implode('', array_slice(file('/home2/cauanb36/deploy-auto.log'), -20));
        }

        return response(
            'docroot_exists='.(is_file($path) ? 'yes' : 'no').' path='.$path."\n".
            'repo_exists='.(is_file($repoPath) ? 'yes' : 'no').' repo_path='.$repoPath."\n".
            'base_path='.base_path()."\n\n".
            "deploy log (last 20 lines):\n".$log
        );
    }

    if (request('debug') === '1') {
        return response('username='.$username.' | password_len='.strlen($password).' | password_first2='.substr($password, 0, 2).' | password_last2='.substr($password, -2));
    }

    try {
        \Illuminate\Support\Facades\Mail::raw('Teste de envio — Banco de Choices ('.now()->toDateTimeString().')', function ($message) use ($to) {
            $message->to($to)->subject('Teste BC debug');
        });

        return response("OK enviado para {$to} (host={$host})");
    } catch (\Throwable $e) {
        return response("ERRO (host={$host}): ".get_class($e).': '.$e->getMessage()."\n\n".$e->getTraceAsString(), 500);
    }
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
