<?php

namespace ApurbaLabs\ApprovalEngine\Tests;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;

use ApurbaLabs\ApprovalEngine\ApprovalEngineServiceProvider;

abstract class TestCase extends BaseTestCase
{
    //use RefreshDatabase; // Handles migration and transactions automatically

    /**
     * This is the "Testbench" way to load migrations. 
     * It prevents the "Table already exists" error.
     */
    protected function defineDatabaseMigrations()
    {
        // Force the Laravel 'users' migration to register first
        // This looks into the vendor folder of the testbench app
        $this->loadLaravelMigrations(); 

        // load your package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // //Load test-specific migrations
        if (is_dir(__DIR__ . '/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/migrations');
        }
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run your Seeders after migrations are finished
        $this->seed(\ApurbaLabs\ApprovalEngine\Database\Seeders\DatabaseSeeder::class);
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
    /*
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    } */
    protected function getEnvironmentSetUp($app)
    {
        // Set the default connection to our 'mysql_test' block
        $app['config']->set('database.default', 'mysql_test');

        // Override the path to point to your package's test modules
        $app['config']->set('approval-engine.modules_path', __DIR__ . '/Modules');
    
        // Override the namespace to match your test modules
        $app['config']->set('approval-engine.modules_namespace', 'ApurbaLabs\\ApprovalEngine\\Tests\\Modules\\');

        $app['config']->set('database.connections.mysql_test', [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'approval_engine_dev_test'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB', 
        ]);


        // Ensure web middleware is present
        $app['router']->aliasMiddleware('auth', \Illuminate\Auth\Middleware\Authenticate::class);


    }

}
