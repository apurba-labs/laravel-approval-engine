<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature;

use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Listeners\HandleBatchApproved;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class EventTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_dispatches_batch_approved_event_when_action_is_executed()
    {
        Event::fake();

        $batch = WorkflowBatch::create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'token' => 'event-token-123',
            'stage' => 1,
            'window_start' => now(),
            'window_end' => now(),
        ]);

        // Trigger the approval action
        app(ApproveBatchAction::class)->execute('event-token-123', 1);

        // Verify the event was dispatched
        Event::assertDispatched(BatchApproved::class, function ($event) use ($batch) {
            return $event->batch->id === $batch->id;
        });
    }

    /** @test */
    public function the_batch_approved_event_has_listener_attached()
    {
        $dispatcher = app(\Illuminate\Contracts\Events\Dispatcher::class);
        
        $this->assertTrue(
            $dispatcher->hasListeners(BatchApproved::class),
            'The BatchApproved event does not have the listener registered in ServiceProvider.'
        );
    }

    /** @test */
    public function listener_sends_batch_approval_mail_successfully()
    {
        Mail::fake();

        //Create a dummy batch
        $batch = WorkflowBatch::create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'stage' => 1,
            'token' => 'testtoken',
            'window_start' => now()->subDay(),
            'window_end' => now()
        ]);

        //Mock the event
        $event = new BatchApproved($batch);

        //Manually trigger the listener
        $listener = new HandleBatchApproved();
        $listener->handle($event);

        //Verify Mail was sent
        Mail::assertSent(BatchApprovalMail::class);
    }
}
