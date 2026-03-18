<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        // Default to SQLite for this specific lifecycle test
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Package Configs
        $app['config']->set('approval-engine.modules_path', __DIR__ . '/Modules');
        $app['config']->set('approval-engine.modules_namespace', 'ApurbaLabs\\ApprovalEngine\\Tests\\Modules\\');
    }

        /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
    }

}
