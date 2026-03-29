<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRuleResolver;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use Illuminate\Support\Facades\Log;

class HandleWorkflowStarted
{
    public function handle(WorkflowStarted $event)
    {
        $ruleResolver = app(WorkflowRuleResolver::class);
        $recipientResolver = app(WorkflowRecipientResolver::class);
        $notificationService = app(NotificationService::class);

        foreach ($event->workflows() as $workflow) {

            // Ensure fresh + valid model
            $workflow = $workflow?->fresh();

            if (!$workflow || !$workflow->id) {
                Log::error("Invalid workflow structure", ['workflow' => $workflow]);
                continue;
            }

            // Resolve stage
            $stage = $ruleResolver->resolveNextStage($workflow);

            if (!$stage) {
                Log::error("No stage resolved", ['workflow_id' => $workflow->id]);
                continue;
            }

            // Find matching rule
            $rule = $ruleResolver->findMatchingRule($workflow);

            if (!$rule) {
                Log::warning("No matching rule found", ['workflow_id' => $workflow->id]);
                continue;
            }

            // Resolve recipient
            $recipient = $recipientResolver->resolve($rule);

            if (!$recipient) {
                Log::error("Recipient not found", [
                    'workflow_id' => $workflow->id,
                    'role' => $rule->role
                ]);
                continue;
            }

            // Prevent duplicate approvals
            $exists = WorkflowApproval::where('workflow_instance_id', $workflow->id)
                ->where('stage_order', $stage?->stage_order)
                ->exists();

            if ($exists) {
                Log::warning("Approval already exists", ['workflow_id' => $workflow->id]);
                continue;
            }

            // Create approval
            $approval = WorkflowApproval::create([
                'workflow_instance_id' => $workflow->id,
                'user_id' => $recipient->id,
                'stage_id' => $stage?->id,
                'stage_order' => $stage?->stage_order,
                'status' => 'pending',
                'assigned_at' => $now,
                'due_at' => $now->copy()->addHours(1), // default SLA
            ]);

            // Create notification
            $notification = $notificationService->createNotification(
                $workflow,
                $rule->role,
                $recipient
            );

            // Send if instant
            $notificationService->dispatch($notification);

            Log::info("Workflow assigned", [
                'workflow_id' => $workflow->id,
                'user_id' => $recipient->id,
                'role' => $rule->role
            ]);
        }
    }
}