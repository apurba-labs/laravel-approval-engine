<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowLog;
use Illuminate\Support\Facades\Log;

class HandleWorkflowCompleted
{
    public function handle(WorkflowCompleted $event)
    {
        $workflow = $event->workflow; 

        if (!$workflow || !$workflow->id) {
            Log::error('Invalid workflow in WorkflowCompleted event');
            return;
        }

        // Timeline log
        WorkflowLog::create([
            'workflow_instance_id' => $workflow->id,
            'module' => $workflow->module,
            'role' => 'completed',
            'stage_order' => $workflow->current_stage_order,
            'entered_at' => now(),
        ]);

        Log::info("Workflow COMPLETED: Workflow #{$workflow->id}");
    }
}