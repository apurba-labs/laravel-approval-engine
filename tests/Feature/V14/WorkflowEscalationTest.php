<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

class WorkflowEscalationTest extends TestCase
{

    /** @test 
     * @group v1.4
    */
    public function it_escalates_when_sla_is_breached()
    {
        // Freeze time
        Carbon::setTestNow(now());

        // Create workflow instance
        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'status' => 'pending',
            'current_stage_order'=>1,
            'payload' => ['amount' => 15000],
        ]);

        // Create approval with expired SLA
        $approval = WorkflowApproval::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => 1,
            'status' => 'pending',
            'assigned_at' => now()->subHours(2),
            'due_at' => now()->subMinutes(1), // already expired
        ]);

        // Run command
        $this->artisan('workflow:process-notifications');

        // Assert escalation notification created
        $this->assertDatabaseHas('workflow_notifications', [
            'workflow_instance_id' => $workflow->id,
            'module' => 'expense',
            'role' => 'admin', // fallback role
        ]);
    }
    /** @test 
     * @group v1.4
    */
    public function it_does_not_escalate_if_sla_not_expired()
    {
        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'status' => 'pending',
            'current_stage_order'=>1,
            'payload' => [],
        ]);

        WorkflowApproval::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => 1,
            'status' => 'pending',
            'assigned_at' => now(),
            'due_at' => now()->addHours(1), // NOT expired
        ]);

        $this->artisan('workflow:process-notifications');

        $this->assertDatabaseCount('workflow_notifications', 0);
    }
}