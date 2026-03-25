<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Listeners\HandleBatchApproved;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

use ApurbaLabs\ApprovalEngine\Models\Support\Requisition;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Illuminate\Support\Facades\Event;

use Illuminate\Support\Facades\Notification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification;

class EventTest extends TestCase
{
    /** @test 
     * @group v1
    */
    public function batch_command_sends_notification_successfully()
    {
        Notification::fake();

        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

        WorkflowSetting::factory()->create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'frequency' => 'daily',
            'send_time' => '00:00',
            'is_active' => 1,
        ]);

        $workflow = WorkflowInstance::factory()->create([
            'module' => 'requisition',
            'payload' => [
                'user_id' => $user->id
            ]
        ]);

        WorkflowNotification::factory()->create([
            'workflow_instance_id' => $workflow->id,
            'module' => 'requisition',
            'role' => 'HOSD',
            'status' => 'pending',
            'recipient_id' => $user->id,
            'recipient_type' => User::class,
            'created_at' => now()->subMinutes(5),
        ]);

        $this->artisan('approval:send-batch --force');

        Notification::assertSentTo(
            $user,
            WorkflowBatchNotification::class
        );
    }


}
