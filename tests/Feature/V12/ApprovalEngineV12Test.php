<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V12;
use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;

class ApprovalEngineV12Test extends TestCase
{

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
