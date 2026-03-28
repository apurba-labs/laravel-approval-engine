<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

class WorkflowMetricsTest extends TestCase
{

    /** @test 
     * @group v1.4
    */
    public function it_calculates_metrics_correctly()
    {
        Carbon::setTestNow(now());

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'status' => 'pending',
            'current_stage_order'=>1,
            'payload' => [],
        ]);

        // Completed approval (normal)
        WorkflowApproval::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => 1,
            'status' => 'approved',
            'assigned_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(5),
            'due_at' => now()->addMinutes(10),
        ]);

        // SLA breach
        WorkflowApproval::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => 2,
            'status' => 'approved',
            'assigned_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(1),
            'due_at' => now()->subMinutes(10),
        ]);

        // Pending
        WorkflowApproval::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => 3,
            'status' => 'pending',
            'assigned_at' => now(),
            'due_at' => now()->addMinutes(30),
        ]);

        // Call API
        $response = $this->getJson('/api/v1/workflow/metrics');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'avg_approval_time',
                'sla_breaches',
                'pending',
            ]
        ]);

        $data = $response->json('data');

        // Assertions
        $this->assertEquals(1, $data['sla_breaches']);
        $this->assertEquals(1, $data['pending']);

        $this->assertGreaterThan(0, $data['avg_approval_time'], "Average approval time should be positive.");
    }
}