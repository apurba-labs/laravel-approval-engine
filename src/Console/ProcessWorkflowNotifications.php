<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Jobs\SendWorkflowNotificationJob;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Services\WorkflowEscalationService;

class ProcessWorkflowNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:process-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process retries and escalations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retry failed
        WorkflowNotification::where('status', 'failed')
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now())
            ->each(function ($notification) {
                dispatch(new SendWorkflowNotificationJob($notification));
            });

        // Escalation
        app(WorkflowEscalationService::class)
            ->processEscalations();

        $this->info('Processed workflow notifications.');
    }
}
