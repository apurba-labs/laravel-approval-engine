<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Jobs\SendWorkflowNotificationJob;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Services\WorkflowEscalationService;

class ProcessWorkflowNotifications extends Command
{
    protected $signature = 'workflow:process-notifications';

    protected $description = 'Process workflow notification retries and escalations';

    public function handle(): int
    {
        $retried = 0;

        WorkflowNotification::query()
            ->where('status', 'failed')
            ->whereColumn('retry_count', '<', 'max_retries')
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now())
            ->each(function ($notification) use (&$retried) {
                dispatch(new SendWorkflowNotificationJob($notification));

                $retried++;
            });

        $escalated = app(WorkflowEscalationService::class)
            ->processEscalations();

        $this->info(
            "Processed workflow notifications. Retried: {$retried}, Escalated: {$escalated}"
        );

        return self::SUCCESS;
    }
}