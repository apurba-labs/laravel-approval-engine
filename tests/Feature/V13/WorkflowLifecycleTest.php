<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V13;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

use ApurbaLabs\ApprovalEngine\Models\{WorkflowRule, WorkflowInstance, WorkflowLog, WorkflowNotification, WorkflowSetting, WorkflowStage};
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowSingleNotification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification;
use Illuminate\Support\Str;

use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowLifecycleTest extends TestCase
{
    use InteractsWithIAM;

    /** @test
     * @group v1.3
     */
    public function it_executes_the_full_v13_instance_lifecycle()
    {
        Notification::fake();

        $roleName = 'COO';
        $scopeId = null;
        $permissionStr = 'approval.finance.approve';
        
        // Create User using the refined helper
        $user = $this->createUserWithPermission($roleName, $scopeId, [$permissionStr]);

        WorkflowStage::factory()->create([
            'module' => 'purchase',
            'stage_order' => 1,
            'role' => 'COO',
            'assign_type' => 'permission',
            'assign_value' => 'approval.finance.approve',
        ]);

        WorkflowRule::factory()
            ->forModule('purchase')
            ->forField('total_amount')
            ->withOperator('>')
            ->withValue('5000')
            ->targetRole('COO')
            ->withPriority(10)
            ->create([
                'assign_type' => 'permission',
                'assign_value' => 'approval.finance.approve',
            ]);

        WorkflowSetting::factory()->create([
            'module' => 'purchase',
            'role' => 'COO',
            'frequency' => 'instant',
            'is_active' => 1,
            'send_time' => '00:00:00',
            'assign_type' => 'permission',
            'assign_value' => 'approval.finance.approve',
        ]);

        $engine = app(WorkflowEngine::class);

        $instance = $engine->start('purchase', [
            'total_amount' => 7500,
        ]);

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instance->id,
            'module' => 'purchase',
            'current_stage_order' => 1,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_instance_id' => $instance->id,
            'role' => 'COO',
            'stage_order' => 1,
        ]);

        $this->assertDatabaseHas('workflow_notifications', [
            'workflow_instance_id' => $instance->id,
            'role' => 'COO',
            'assign_type' => 'permission',
            'assign_value' => 'approval.finance.approve',
            'status' => 'sent',
            'recipient_id' => $user->id,
        ]);

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
        $this->expectNotToPerformAssertions();
         /*
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

        WorkflowRule::factory()
            ->forModule('purchase')
            ->forField('total_amount')
            ->withOperator('>')
            ->withValue(10000)
            ->targetRole('HOSD')
            ->create();

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
            WorkflowBatchNotification::class
        );
        */
    }
}
