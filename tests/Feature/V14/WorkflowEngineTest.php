<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Services\WorkflowManager;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowNotification;

use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowEngineTest extends TestCase
{
    use InteractsWithIAM;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupWorkflowEnvironment(); 
    }

    /** @test */
    public function it_can_start_a_workflow_and_create_initial_records()
    {

        $manager = app(WorkflowManager::class);

        $workflow = $manager->start('requisition', [
            'total_amount' => 5000,
        ]);

        $this->assertInstanceOf(WorkflowInstance::class, $workflow);

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $workflow->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('workflow_logs', 1);

        $this->assertDatabaseCount('workflow_approvals', 1);

        $this->assertDatabaseCount('workflow_notifications', 1);
    }

    /** @test */
    public function it_can_approve_and_move_to_next_stage()
    {
        $manager = app(WorkflowManager::class);

        $workflow = $manager->start('requisition', [
            'amount' => 5000,
        ]);

        $approval = WorkflowApproval::where('workflow_instance_id', $workflow->id)
        ->where('status', 'pending')
        ->first();

        $manager->approve($workflow->id, $approval->user_id);

        $workflow->refresh();

        $this->assertEquals('pending', $workflow->status); // next stage

        $this->assertDatabaseCount('workflow_logs', 2);
    }

    /** @test */
    public function it_completes_workflow_when_last_stage_is_done()
    {
        $manager = app(WorkflowManager::class);

        $workflow = $manager->start('requisition', [
            'amount' => 5000,
        ]);

        while ($workflow->status === 'pending') {

            $approval = WorkflowApproval::where('workflow_instance_id', $workflow->id)
                ->where('status', 'pending')
                ->first();

            $manager->approve($workflow->id, $approval->user_id);

            $workflow->refresh();
        }

        $this->assertEquals('completed', $workflow->status);
    }
}