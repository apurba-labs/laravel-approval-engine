<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Services\PluginManager;
use ApurbaLabs\ApprovalEngine\Support\BasePlugin;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;

class PluginSystemTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        app(PluginManager::class)->clear();
    }
    
    /** @test */
    public function it_boots_plugin()
    {
        $plugin = new class extends BasePlugin {
            public bool $booted = false;

            public function boot(): void
            {
                $this->booted = true;
            }
        };

        app(PluginManager::class)->register($plugin);
        app(PluginManager::class)->boot();

        $this->assertTrue($plugin->booted);
    }

    /** @test */
    public function it_receives_event()
    {
        $workflow = WorkflowInstance::factory()->create();

        $plugin = new class extends BasePlugin {
            public bool $called = false;

            public function boot(): void
            {
                $this->listen(WorkflowCompleted::class, function () {
                    $this->called = true;
                });
            }
        };

        app(PluginManager::class)->register($plugin);
        app(PluginManager::class)->boot();

        event(new WorkflowCompleted($workflow));

        $this->assertTrue($plugin->called);
    }

    /** @test */
    public function it_does_not_break_on_plugin_failure()
    {
        $workflow = WorkflowInstance::factory()->create();

        $plugin = new class extends BasePlugin {
            public function boot(): void
            {
                $this->listen(WorkflowCompleted::class, function () {
                    throw new \Exception('Plugin failed');
                });
            }
        };

        app(PluginManager::class)->register($plugin);
        app(PluginManager::class)->boot();

        // Should NOT throw exception
        event(new WorkflowCompleted($workflow));

        $this->assertTrue(true);
    }
}