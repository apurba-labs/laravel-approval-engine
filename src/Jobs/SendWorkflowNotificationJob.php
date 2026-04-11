<?php

namespace ApurbaLabs\ApprovalEngine\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Services\WorkflowRetryService;

class SendWorkflowNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public WorkflowNotification $notification
    ) {}

    public function handle(): void
    {
        try {
            /**
             * TODO:
             * Replace with real mail / SMS / push dispatch later
             */

            $this->notification->update([
                'is_sent' => true,
                'sent_at' => now(),
                'status' => 'sent',
                'next_retry_at' => null,
                'error' => null,
            ]);

        } catch (Exception $e) {

            app(WorkflowRetryService::class)
                ->scheduleRetry($this->notification);

            throw $e;
        }
    }
}