<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowEscalationTest extends TestCase
{
    use InteractsWithIAM;
    /** @test 
     * @group v1.4
    */
    public function it_escalates_when_sla_is_breached()
    {
        // Freeze time
        Carbon::setTestNow(now());

        
        // Create User using the refined helper
        $user = $this->createUserWithPermission('admin');

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'status' => 'pending',
            'current_stage_order'=>1,
            'payload' => [],
        ]);

        WorkflowNotification::create([
            'workflow_instance_id' => $workflow->id,
            'module' => 'expense',
            'status' => 'pending',
            'stage_order' => 1,
            'assign_type' => 'role',
            'assign_value' => 'admin',
            'escalate_at' => now()->subMinute(),
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'payload' => [
                'amount' => 15000,
            ],
            'current_stage_order' => 1,
            'status' => 'pending',
        ]);

        WorkflowNotification::create([
            'workflow_instance_id' => $workflow->id,
            'module' => 'expense',
            'status' => 'pending',
            'stage_order' => 1,
            'assign_type' => 'role',
            'assign_value' => 'admin',
            'escalate_at' => now()->subMinute(),
        ]);
        // Run command
        $this->artisan('workflow:process-notifications');

        // Assert escalation notification created
        $this->assertDatabaseHas('workflow_notifications', [
            'workflow_instance_id' => $workflow->id,
            'assign_type' => 'role',
            'assign_value' => 'admin',
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