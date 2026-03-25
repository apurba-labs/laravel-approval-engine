<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

use Illuminate\Support\Facades\Log;

use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

class HandleWorkflowStarted
{
    /**
     * Handle the event.
     * We only handle non-batch (single) starts here
     *
     * @param WorkflowStarted $event
     * @return void
     */
    public function handle(WorkflowStarted $event)
    {

        $stageNavigator = app(StageNavigator::class);
        $engine = app(WorkflowEngine::class);
        $notificationService = app(NotificationService::class);

        foreach ($event->workflows() as $workflow) {

            if (!isset($workflow->id)) {
                Log::error("Invalid workflow structure", ['workflow' => $workflow]);
                continue;
            }

            
            $stage = $stageNavigator->getCurrentStage(
                $workflow->module,
                $workflow->current_stage_order
            );

            if (!$stage) {
                Log::error("Stage not found for module {$workflow->module}");
                continue;
            }

            $module = $engine->getModule($workflow->module);

            $recipient = $module->resolveRecipient($workflow, $stage->role);
            
            $notification = WorkflowNotification::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $workflow->module,
                'role' => $stage->role,
                'recipient_id' => $recipient?->id,
                'recipient_type' => $recipient ? get_class($recipient) : null,
                'status' => 'pending',
            ]);

            // optional: instant send
            $notificationService->sendImmediateIfNeeded($notification);
            
            Log::info("New Workflow Started: {$workflow->module}");
        }
    }
}
