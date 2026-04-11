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
use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class EventTest extends TestCase
{
    use InteractsWithIAM;
    /** @test 
     * @group v1
    */
    public function batch_command_sends_notification_successfully()
    {
        Notification::fake();

        $user = $this->createUserWithPermission('hosd');

        WorkflowSetting::factory()->create([
            'module' => 'requisition',
            'role' => 'hosd',
            'frequency' => 'daily',
            'is_active' => 1,
            'send_time' => '00:00:00',
            'assign_type' => 'role',
            'assign_value' => 'hosd',
        ]);

        $workflow = WorkflowInstance::factory()->create([
            'module' => 'requisition',
            'status' => 'pending',
            'current_stage_order'=>1,
            'payload' => [
                'user_id' => $user->id
            ]
        ]);

        WorkflowNotification::create([
            'workflow_instance_id' => $workflow->id,
            'role' => 'hosd',
            'module' => 'requisition',
            'status' => 'pending',
            'stage_order' => 1,
            'assign_type' => 'role',
            'assign_value' => 'hosd',
            'recipient_signature' => 'role:hosd', 
            'recipient_id' => $user->id,
            'recipient_type' => get_class($user),
        ]);

        $this->artisan('approval:send-batch --force');

        Notification::assertSentTo(
            $user,
            WorkflowBatchNotification::class
        );
    }


}
