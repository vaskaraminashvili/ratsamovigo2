<?php

namespace Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * A cached config ignores phpunit.xml, so the tests would run against the real
     * database from .env and RefreshDatabase would wipe it. Refuse to start instead.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if ($app->configurationIsCached()) {
            throw new RuntimeException('Config is cached, so tests would use the real database. Run "php artisan optimize:clear" and try again.');
        }

        return $app;
    }
}
