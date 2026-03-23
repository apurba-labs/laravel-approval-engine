<?php

namespace Tests\Feature\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApprovalEngineV12Test extends TestCase
{
    /** 
     * @test 
     * @group v1.2
     */
    public function test_single_start_triggers_correct_threshold_logic()
    {
        $response = ApprovalEngine::start('requisition', ['total_amount' => 15000]);
        
        $this->assertEquals('success', $response['status']);
        $this->assertDatabaseHas('workflow_batches', ['module' => 'requisition']);
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
        $engine = app(\ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine::class);
        $module = $engine->getModule($batch->module);
        
        $this->assertEquals('creator', $module->relations()[0]);
    }

    
}
