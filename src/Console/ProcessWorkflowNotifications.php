<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
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
                dispatch(new \ApurbaLabs\ApprovalEngine\Jobs\SendWorkflowNotificationJob($notification));
            });

        // Escalation
        WorkflowNotification::where('is_sent', false)
            ->whereNotNull('escalate_at')
            ->where('escalate_at', '<=', now())
            ->each(function ($notification) {
                app(WorkflowEscalationService::class)
                    ->escalate($notification);
            });

        $this->info('Processed workflow notifications.');
    }
}
