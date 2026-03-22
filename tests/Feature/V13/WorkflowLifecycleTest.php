<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V13;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Models\{WorkflowRule, WorkflowInstance, WorkflowLog, WorkflowNotification, WorkflowSetting};
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use Illuminate\Support\Facades\Notification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowSingleNotification;

class WorkflowLifecycleTest extends TestCase
{
    /** @test 
     * @group v1.3
    */
    public function it_executes_the_full_v13_instance_lifecycle()
    {
        Notification::fake();

        // 1. Setup: Create a Rule that routes > 5000 to 'COO'
        WorkflowRule::create([
            'module' => 'purchase',
            'field' => 'total_amount',
            'operator' => '>',
            'value' => '5000',
            'role' => 'COO',
            'priority' => 10
        ]);

        // Setup: Create an 'instant' notification setting for COO
        WorkflowSetting::create([
            'module' => 'purchase',
            'role' => 'COO',
            'frequency' => 'instant',
            'is_active' => true
        ]);

        $engine = app(WorkflowEngine::class);
        $data = ['total_amount' => 7500, 'description' => 'Test Purchase'];

        // --- TEST 1 & 2: Start & Rule Resolver ---
        $instance = $engine->start('purchase', $data);

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instance->id,
            'module' => 'purchase',
            'role' => 'COO', // Verify Rule Resolver worked (Test 2)
            'status' => 'pending'
        ]);

        // --- TEST 5: Metrics & Logs ---
        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $instance->id,
            'role' => 'COO',
            'entered_at' => now()->toDateTimeString()
        ]);

        // --- TEST 3: Notification & Instant Queue ---
        // Verify the notification record exists
        $this->assertDatabaseHas('workflow_notifications', [
            'workflow_instance_id' => $instance->id,
            'role' => 'COO',
            'is_sent' => true // Should be true because frequency was 'instant'
        ]);

        // Verify the actual Mail/Notification was sent
        Notification::assertSentTo(
            new \ApurbaLabs\ApprovalEngine\Tests\Models\User(), // Or your config user
            WorkflowSingleNotification::class
        );
    }

    /** @test 
     * @group v1.3
    */
    public function it_batches_notifications_correctly_for_non_instant_settings()
    {
        // Setup: Set frequency to 'daily'
        WorkflowSetting::create([
            'module' => 'purchase', 'role' => 'HOD', 'frequency' => 'daily'
        ]);

        $engine = app(WorkflowEngine::class);
        
        // Start two workflows
        $engine->start('purchase', ['total_amount' => 100]);
        $engine->start('purchase', ['total_amount' => 200]);

        // --- TEST 4: Batching ---
        // Run your Artisan command
        $this->artisan('approval:send-batch')->assertExitCode(0);

        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'purchase',
            'role' => 'HOD',
            'item_count' => 2
        ]);

        // Check that notifications are now linked to the batch
        $this->assertEquals(0, WorkflowNotification::where('is_sent', false)->count());
    }
}
