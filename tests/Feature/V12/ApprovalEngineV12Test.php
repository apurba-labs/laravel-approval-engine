<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V12;
use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Services\ModuleRegistry;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Tests\Support\Modules\PurchaseModule;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;

class ApprovalEngineV12Test extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        app(ModuleRegistry::class)->register(
            new PurchaseModule()
        );
    }

    /** 
     * @test 
     * @group v1.2
     */
    public function test_signature_resolver_identifies_correct_owner_type()
    {

        // Setup a fake batch and record
        $batch = WorkflowBatch::factory()->create(['module' => 'purchase']);
        
        // This tests the 'ownerRelations' signature we built
        $engine = app(WorkflowEngine::class);
        $module = $engine->getModule($batch->module);

        $this->assertEquals('user', $module->relations()[0]);

        
    }

    
}
