<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use RuntimeException;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;

class WorkflowEscalationService
{
    public function processEscalations(): int
    {
        $processed = 0;

        WorkflowNotification::query()
            ->where('status', 'pending')
            ->whereNull('escalated_at')
            ->whereNotNull('escalate_at')
            ->where('escalate_at', '<=', now())
            ->each(function (WorkflowNotification $notification) use (&$processed) {

                $workflow = $notification->workflowInstance;

                if (!$workflow) {
                    return;
                }

                $stage = app(StageNavigator::class)
                    ->getCurrentStage(
                        $workflow->module,
                        $notification->stage_order
                    );

                if (!$stage) {
                    return;
                }

                // Override assignment with escalation target
                $stage->resolved_assign_type =
                    $notification->escalate_assign_type;

                $stage->resolved_assign_value =
                    $notification->escalate_assign_value;

                $recipient = app(WorkflowRecipientResolver::class)
                    ->resolve($stage, $workflow);

                if (!$recipient) {
                    throw new RuntimeException(
                        "Escalation recipient could not be resolved for notification {$notification->id}"
                    );
                }

                $escalatedNotification = app(NotificationService::class)
                    ->createNotification(
                        workflow: $workflow,
                        stage: $stage,
                        recipient: $recipient
                    );

                app(NotificationService::class)
                    ->sendImmediateIfNeeded($escalatedNotification);

                $notification->update([
                    'status' => 'escalated',
                    'escalated_at' => now(),
                ]);

                $processed++;
            });

        return $processed;
    }
}