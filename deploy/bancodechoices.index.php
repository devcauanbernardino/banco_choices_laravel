<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../repositories/banco_choices_laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../repositories/banco_choices_laravel/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../repositories/banco_choices_laravel/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
