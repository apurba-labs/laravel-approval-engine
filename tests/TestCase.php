<?php

namespace ApurbaLabs\ApprovalEngine\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;

use Illuminate\Support\Facades\Schema;

use ApurbaLabs\ApprovalEngine\ApprovalEngineServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase; // Handles migration reset + transactions automatically
    // use \Illuminate\Foundation\Testing\DatabaseTransactions; // Keep data isolated per test

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = false;

    /**
     * Get the path to the .env testing file.
     *
     * @return string
     */
    protected function getEnvironmentFilePath()
    {
        return __DIR__ . '/../.env.testing';
    }

    /**
     * This is the "Testbench" way to load migrations. 
     * It prevents the "Table already exists" error.
     */
    protected function defineDatabaseMigrations()
    {
        // Load Laravel's default migrations path so migrate:fresh runs users table migration.
        $this->loadMigrationsFrom(\Orchestra\Testbench\default_migration_path());

        // Force absolute verified path for package helper migrations (roles, purchases, requisitions etc.)
        $path = realpath(__DIR__ . '/Support/Migrations');

        // Load your Support/Mock migrations (Roles, Purchases, etc.)
        $this->loadMigrationsFrom($path);

        // Load your package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Seed your package after migrations are complete.
        // $this->seed(\ApurbaLabs\ApprovalEngine\Database\Seeders\WorkflowDatabaseSeeder::class);
    }

    /**
     * Automatically seed each test with this seeder.
     * @return array<int,string>
     */
    protected function defineDatabaseSeeders()
    {
        return [
            \ApurbaLabs\ApprovalEngine\Database\Seeders\WorkflowDatabaseSeeder::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
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
        // Use testing connection with MySQL for RefreshDatabase compliance
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'approval-engine-dev-test'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', '1234@abcd'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB',
        ]);

        // Override the path to point to your package's test modules
        $app['config']->set('approval-engine.modules_path', __DIR__ . '/Support/Modules');
    
        // Override the namespace to match your test modules
        $app['config']->set('approval-engine.modules_namespace', 'ApurbaLabs\\ApprovalEngine\\Tests\\Support\\Modules\\');

        // Set the User model to my package's test model
        $app['config']->set('auth.providers.users.model', \ApurbaLabs\ApprovalEngine\Tests\Support\Models\User::class);

        // Ensure web middleware is present
        $app['router']->aliasMiddleware('auth', \Illuminate\Auth\Middleware\Authenticate::class);
    }

}
