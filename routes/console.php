<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Cron em produção (no servidor): * * * * * cd /caminho/da/app && php artisan schedule:run >> /dev/null 2>&1
| Quando existirem tarefas agendadas, registe-as com Illuminate\Support\Facades\Schedule em bootstrap ou um Service Provider.
| Exemplos:
|
| Schedule::command('queue:prune-failed --hours=48')->daily();
|
*/
