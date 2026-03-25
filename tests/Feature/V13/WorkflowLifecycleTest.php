<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V13;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

use ApurbaLabs\ApprovalEngine\Models\{WorkflowRule, WorkflowInstance, WorkflowLog, WorkflowNotification, WorkflowSetting, WorkflowStage};
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowSingleNotification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification;

class WorkflowLifecycleTest extends TestCase
{

    /** @test 
     * @group v1.3
    */
    public function it_executes_the_full_v13_instance_lifecycle()
    {
        Notification::fake();
        
        // Create user (recipient)
        $user = Role::where('name', 'COO')->first()?->users()->first() ?? User::factory()->withRole('COO')->create();

        WorkflowStage::factory()->create([
            'module' => 'purchase',
            'stage_order' => 1,
            'role' => 'COO',
        ]);
        // Rule: purchase > 5000 → COO
        WorkflowRule::factory()
            ->forModule('purchase')
            ->forField('total_amount')
            ->withOperator('>')
            ->withValue('5000')
            ->targetRole('COO')
            ->withPriority(10)
            ->create();

        // Setting: instant notification for COO
        WorkflowSetting::factory()->create([
            'module' => 'purchase',
            'role' => 'COO',
            'frequency' => 'instant', // IMPORTANT
            'is_active' => 1,
            'send_time' => '00:00:00'
        ]);

        $engine = app(WorkflowEngine::class);

        $data = [
            'total_amount' => 7500,
            'user_id' => $user->id // REQUIRED for recipient
        ];

        // --- TEST: Start ---
        $instance = $engine->start('purchase', $data);

        // Rule applied → COO
        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instance->id,
            'module' => 'purchase',
            'current_stage_order' => 1,
            'status' => 'pending'
        ]);

        // --- TEST: Logs ---
        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $instance->id,
            'role' => 'COO',
        ]);

        // --- TEST: Notification created ---
        $this->assertDatabaseHas('workflow_notifications', [
            'workflow_instance_id' => $instance->id,
            'role' => 'COO',
            'status' => 'sent',
            'recipient_id' => $user->id,
        ]);

        // --- TEST: Notification actually sent ---
        Notification::assertSentTo(
            $user,
            WorkflowSingleNotification::class
        );
    }

    /** @test 
     * @group v1.3
    */
    public function it_batches_notifications_correctly_for_non_instant_settings()
    {
        Notification::fake();

        // Create user (recipient)
        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

        // Stage → HOSD
        WorkflowStage::factory()->create([
            'module' => 'purchase',
            'stage_order' => 1,
            'role' => 'HOSD',
        ]);

        // Setting → daily (NOT instant)
        WorkflowSetting::factory()->create([
            'module' => 'purchase',
            'role' => 'HOSD',
            'frequency' => 'daily', // batching mode
            'is_active' => 1,
            'send_time' => '00:00:00'
        ]);

        $engine = app(WorkflowEngine::class);

        // Start two workflows
        $engine->start('purchase', [
            'total_amount' => 100,
            'user_id' => $user->id
        ]);

        $engine->start('purchase', [
            'total_amount' => 200,
            'user_id' => $user->id
        ]);

        // --- ASSERT: Notifications created but NOT sent yet ---
        $this->assertEquals(
            2,
            WorkflowNotification::where('status', 'pending')->count()
        );

        // --- RUN BATCH COMMAND ---
        $this->artisan('approval:send-batch')->assertExitCode(0);

        // --- ASSERT: Batch created ---
        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'purchase',
            'role' => 'HOSD',
            'item_count' => 2
        ]);

        // --- ASSERT: Notifications marked as sent ---
        $this->assertEquals(
            0,
            WorkflowNotification::where('status', 'pending')->count()
        );

        $this->assertEquals(
            2,
            WorkflowNotification::where('status', 'sent')->count()
        );

        // --- ASSERT: Batch notification sent ---
        Notification::assertSentTo(
            $user,
            \ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification::class
        );
    }
}
