<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Services\ModuleRegistry;
use ApurbaLabs\ApprovalEngine\Tests\Support\Modules\TestWorkflowModule;

class ModuleRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        app(ModuleRegistry::class)->clear();
    }

    /** @test */
    public function it_registers_module()
    {
        $registry = app(ModuleRegistry::class);

        $module = new TestWorkflowModule();

        $registry->register($module);

        $this->assertNotNull($registry->get('requisition'));
    }

    /** @test */
    public function it_resolves_module_correctly()
    {
        $registry = app(ModuleRegistry::class);

        $module = new TestWorkflowModule();

        $registry->register($module);

        $resolved = $registry->get('requisition');

        $this->assertInstanceOf(TestWorkflowModule::class, $resolved);
    }

    /** @test */
    public function it_returns_null_for_missing_module()
    {
        $registry = app(ModuleRegistry::class);

        $this->assertNull($registry->get('invalid'));
    }
}