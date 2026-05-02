<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Fix inferBasePath() when vários autoloaders (ex.: PHPUnit) fazem o Laravel resolver um diretório errado.
     */
    public function createApplication()
    {
        $basePath = dirname(__DIR__);
        $_ENV['APP_BASE_PATH'] = $basePath;
        $_SERVER['APP_BASE_PATH'] = $basePath;

        return parent::createApplication();
    }
}
