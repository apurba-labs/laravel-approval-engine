<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use Illuminate\Support\Facades\Log;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRuleResolver;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;
/**
 * This listener handles the WorkflowStarted event, which is triggered when a new workflow instance is created.
 * It determines the appropriate stage and recipient for the workflow, creates the necessary approval records,
 * and sends notifications to the assigned users.
 *
 * The listener performs the following steps:
 * 1. Listens for the WorkflowStarted event.
 * 2. For each workflow instance in the event:
 *    a. Resolves the next stage using WorkflowRuleResolver.
 *    b. Resolves the recipient for the stage using WorkflowRecipientResolver.
 *    c. Creates a WorkflowApproval record to track the approval status.
 *    d. Creates a WorkflowNotification record to notify the recipient with assignment snapshot and stage snapshot.
 *    e. Logs any errors encountered during processing.
 *
 * This implementation ensures that when a workflow is started, it is properly assigned to the correct users based on
 * the defined rules and stages, and that they are notified accordingly.
 */
class HandleWorkflowStarted
{
    public function handle(WorkflowStarted $event)
    {
        try {
            $ruleResolver = app(WorkflowRuleResolver::class);
            $recipientResolver = app(WorkflowRecipientResolver::class);
            $notificationService = app(NotificationService::class);

            foreach ($event->workflows() as $workflow) {

                $workflow = $workflow?->fresh();

                if (!$workflow || !$workflow->id) {
                    Log::error('Invalid workflow structure', [
                        'workflow' => $workflow,
                    ]);
                    continue;
                }

                // Resolve stage
                $stage = $ruleResolver->resolveNextStage($workflow);

                if (!$stage) {
                    Log::error('No stage resolved', [
                        'workflow_id' => $workflow->id,
                    ]);
                    continue;
                }

                // Resolve recipient - supports dynamic resolution based on workflow rules
                $recipient = $recipientResolver->resolve($stage, $workflow);

                if (!$recipient) {
                    Log::error('Recipient not found', [
                        'workflow_id' => $workflow->id,
                    ]);
                    continue;
                }

                $now = now();

                // Create Approval record
                WorkflowApproval::create([
                    'workflow_instance_id' => $workflow->id,
                    'user_id' => $recipient->id,
                    'stage_id' => $stage->id,
                    'stage_order' => $stage->stage_order,
                    'status' => 'pending',
                    'assigned_at' => $now,
                    'due_at' => $now->copy()->addHours(24), // SLA
                ]);

                // Create Notification
                $notification = $notificationService->createNotification(
                    workflow: $workflow,
                    stage: $stage,
                    recipient: $recipient
                );

                // Send if instant
                $notificationService->sendImmediateIfNeeded($notification);

                Log::info('Workflow assigned', [
                    'workflow_id' => $workflow->id,
                    'recipient_id' => $recipient->id,
                    'stage_id' => $stage->id,
                ]);
            }
            
         } catch (\Exception $e) {
            Log::error('Error handling WorkflowStarted event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}