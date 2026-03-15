<?php

namespace ApurbaLabs\ApprovalEngine\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use ApurbaLabs\ApprovalEngine\ApprovalEngineServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // This runs package migrations automatically in the in-memory DB
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Define package providers.
     * (Equivalent to adding to config/app.php)
     */
    protected function getPackageProviders($app)
    {
        return [
            ApprovalEngineServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     * (Forces SQLite in-memory for all tests)
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
