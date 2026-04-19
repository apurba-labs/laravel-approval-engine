<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use Illuminate\Support\Facades\Log;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStageAdvanced;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Contracts\NotificationInterface;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;

class HandleWorkflowStageAdvanced
{
    public function handle(WorkflowStageAdvanced $event): void
    {
        try {
            $workflow = $event->workflow;

            if (!$workflow || !$workflow->id) {
                Log::error('Invalid workflow in WorkflowStageAdvanced event');
                return;
            }

            $stageNavigator = app(StageNavigator::class);
            $recipientResolver = app(WorkflowRecipientResolver::class);
            $notificationService = app(NotificationInterface::class);

            // Resolve CURRENT stage
            $stage = $stageNavigator->getCurrentStage(
                $workflow->module,
                $workflow->current_stage_order
            );

            if (!$stage) {
                Log::error('Stage not found after transition', [
                    'workflow_id' => $workflow->id,
                    'stage_order' => $workflow->current_stage_order,
                ]);
                return;
            }

            // Resolve recipient
            $recipient = $recipientResolver->resolve($stage, $workflow);

            if (!$recipient) {
                Log::error('Recipient not found for next stage', [
                    'workflow_id' => $workflow->id,
                    'stage_id' => $stage->id,
                ]);
                return;
            }

            $exists = WorkflowApproval::where([
                'workflow_instance_id' => $workflow->id,
                'stage_order' => $stage->stage_order,
            ])->exists();

            if ($exists) {
                return; // idempotent guard
            }

            $now = now();

            // Create NEXT approval
            WorkflowApproval::create([
                'workflow_instance_id' => $workflow->id,
                'user_id' => $recipient->id,
                'stage_id' => $stage->id,
                'stage_order' => $stage->stage_order,
                'status' => 'pending',
                'assigned_at' => $now,
                'due_at' => $now->copy()->addHours(24),
            ]);

            // Create notification
            $notification = $notificationService->createNotification(
                workflow: $workflow,
                stage: $stage,
                recipient: $recipient
            );

            // Dispatch + maybe send
            $notificationService->dispatchAndMaybeSend($notification);

            Log::info('Workflow stage advanced', [
                'workflow_id' => $workflow->id,
                'from' => $event->fromRole,
                'to' => $event->toRole,
                'stage_id' => $stage->id,
                'recipient_id' => $recipient->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error handling WorkflowStageAdvanced event', [
                'error' => $e->getMessage(),
                'workflow_id' => $event->workflow->id ?? null,
            ]);
        }
    }
}