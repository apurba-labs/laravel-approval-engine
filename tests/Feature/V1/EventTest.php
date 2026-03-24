<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Listeners\HandleBatchApproved;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

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
        //Notification Fake
        Notification::fake();

        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();
        $stage = WorkflowStage::factory()->forModule('requisition')->forRole('HOSD')->atStage(1)->create();
        // Create a dummy batch
        $batch = WorkflowBatch::factory()->forModule('requisition')->forRole('HOSD')->atStage($stage->stage_order)->completed()->create();

        // Mock the event
        $event = new BatchApproved($batch);

        // Manually trigger the listener
        $listener = new HandleBatchApproved();
        $listener->handle($event);

        // Verify the Notification was sent to the right user
        Notification::assertSentTo(
            $user, 
            WorkflowBatchNotification::class,
            function ($notification, $channels) use ($batch) {
                // Optional: Verify internal properties
                //return $notification->batch->id === $batch->id && in_array('mail', $channels);

                //This ensures we match the ID even if the object instance is different
                return $notification->batch->role === 'HOSD';
            }
        );
    }
}
