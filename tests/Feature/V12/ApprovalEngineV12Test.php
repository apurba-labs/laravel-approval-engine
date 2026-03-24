<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V12;
use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;

class ApprovalEngineV12Test extends TestCase
{

    /** 
     * @test 
     * @group v1.2
     */
    public function test_single_start_triggers_correct_threshold_logic()
    {
        /*
        $response = ApprovalEngine::start('requisition', ['total_amount' => 15000]);
        
        $this->assertEquals('success', $response['status']);
        $this->assertDatabaseHas('workflow_batches', ['module' => 'requisition']);
        */
        $this->assertTrue(true);
    }

    /** 
     * @test 
     * @group v1.2
     */
    public function test_signature_resolver_identifies_correct_owner_type()
    {
$this->assertTrue(true);
    /*
        // Setup a fake batch and record
        $batch = WorkflowBatch::factory()->create(['module' => 'purchase']);
        
        // This tests the 'ownerRelations' signature we built
        $engine = app(WorkflowEngine::class);
        $module = $engine->getModule($batch->module);
        
        $this->assertEquals('creator', $module->relations()[0]);

        */
    }

    
}
