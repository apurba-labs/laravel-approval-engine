<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use Illuminate\Support\Facades\Log;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Contracts\NotificationInterface;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;

class HandleWorkflowStarted
{
    public function handle(WorkflowStarted $event): void
    {
        try {
            $workflow = $event->workflow;

            if (!$workflow || !$workflow->id) {
                Log::error('Invalid workflow in WorkflowStarted');
                return;
            }

            $stageNavigator = app(StageNavigator::class);
            $recipientResolver = app(WorkflowRecipientResolver::class);
            $notificationService = app(NotificationInterface::class);

            // Get CURRENT stage
            $stage = $stageNavigator->getCurrentStage(
                $workflow->module,
                $workflow->current_stage_order
            );

            if (!$stage) {
                Log::error('Stage not found on start', [
                    'workflow_id' => $workflow->id,
                ]);
                return;
            }

            // Resolve recipient only (no rule logic)
            $recipient = $recipientResolver->resolve($stage, $workflow);

            if (!$recipient) {
                Log::error('Recipient not found on start', [
                    'workflow_id' => $workflow->id,
                    'stage_id' => $stage->id,
                ]);
                return;
            }

            // Notification
            $notification = $notificationService->createNotification(
                workflow: $workflow,
                stage: $stage,
                recipient: $recipient
            );

            $notificationService->dispatchAndMaybeSend($notification);

            Log::info('Workflow started notification sent', [
                'workflow_id' => $workflow->id,
                'recipient_id' => $recipient->id,
                'stage_id' => $stage->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error in HandleWorkflowStarted', [
                'error' => $e->getMessage(),
                'workflow_id' => $event->workflow->id ?? null,
            ]);
        }
    }
}