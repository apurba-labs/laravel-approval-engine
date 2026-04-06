<?php

namespace ApurbaLabs\ApprovalEngine\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Services\WorkflowRetryService;

class SendWorkflowNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public WorkflowNotification $notification)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Simulate send (email/sms later)
            // TODO: integrate mail/notification channel

            $this->notification->update([
                'is_sent' => true,
                'sent_at' => now(),
                'status' => 'sent',
            ]);

        } catch (\Exception $e) {

            app(WorkflowRetryService::class)
                ->scheduleRetry($this->notification);

            throw $e; // important for retry later
        }
    }
}
