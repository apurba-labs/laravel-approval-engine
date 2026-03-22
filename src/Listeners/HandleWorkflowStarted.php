<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use Illuminate\Support\Facades\Mail;
//use ApurbaLabs\ApprovalEngine\Mail\SingleWorkflowMail; 
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use Illuminate\Support\Facades\Log;

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
        if ($event->isBatch()) {
            return;
        }

        $stageNavigator = app(StageNavigator::class);

        foreach ($event->getWorkflows() as $workflow) {

            $stage = $stageNavigator->getCurrentStage(
                $workflow->module,
                $workflow->current_stage_order
            );

            $notification = WorkflowNotification::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $workflow->module,
                'role' => $stage->role,
                'status' => 'pending',
            ]);

            $setting = WorkflowSetting::where('module', $instance->module)
                ->where('role', $instance->role)
                ->first();

            if ($setting?->frequency === 'instant') {
                app(NotificationService::class)->send($notification);
            }
            Log::info("New Workflow Started: {$workflow['module']}");
        }
    }
}
