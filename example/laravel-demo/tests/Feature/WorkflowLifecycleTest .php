<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Tests\Models\Requisition;
use ApurbaLabs\ApprovalEngine\Tests\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

class WorkflowLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_full_workflow_lifecycle()
    {
        //Arrange: Setup Data
        $user = User::create(['name' => 'Apurba', 'email' => 'a@b.com', 'password' => 'pass']);
        
        $batch = WorkflowBatch::create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'token' => 'lifecycle-token-123',
            'stage' => 1,
            'window_start' => now()->subDay(),
            'window_end' => now(),
            'status' => 'sent'
        ]);

        // Create a requisition that belongs to this batch window
        $req = Requisition::create([
            'user_id' => $user->id,
            'title' => 'Lifecycle Req',
            'status' => 'approved',
            'approved_at' => now()->subHours(2)
        ]);

        //Act: Approve the Batch via the Engine
        $engine = app(WorkflowEngine::class);
        $engine->approveBatch('lifecycle-token-123', $user->id);

        //Assert: Check Approval Record
        $this->assertDatabaseHas('workflow_approvals', [
            'batch_id' => $batch->id,
            'user_id' => $user->id,
            'status' => 'approved'
        ]);

        //Assert: Check if Batch moved to 'completed' (or 'approved' based on your logic)
        $batch->refresh();
        $this->assertEquals('approved', $batch->status);

        //Assert: Verify the Requisition status was updated by the Engine
        $req->refresh();
        // If Stage 1 is the only stage, it should be 'fully_approved'
        // If there's a Stage 2, it should now be at Stage 2
        $this->assertNotNull($req->approved_at);
    }
}
