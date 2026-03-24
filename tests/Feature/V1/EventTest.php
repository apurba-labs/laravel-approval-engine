<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Listeners\HandleBatchApproved;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
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
    public function listener_sends_batch_approval_notification_successfully()
    {
        Notification::fake();

        WorkflowBatch::query()->delete();
        // Ensure the User exists and has the Role
        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

        $batch = WorkflowBatch::factory()
                ->forModule('requisition')
                ->forRole('HOSD')
                ->create();
        // Act
        event(new BatchApproved($batch));

        // Use a Callback to match by ID/Email to bypass object instance issues
        Notification::assertSentTo(
            $user, 
            WorkflowBatchNotification::class,
            function ($notification) use ($batch) {
                return $notification->batch->id === $batch->id;
            }
        );
    }

}
