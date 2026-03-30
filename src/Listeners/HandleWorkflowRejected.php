<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowRejected;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use Illuminate\Support\Facades\Log;

class HandleWorkflowRejected
{
    public function handle(WorkflowRejected $event)
    {
        $workflow = $event->workflow;

        // TIMELINE LOG
        WorkflowLog::create([
            'workflow_instance_id' => $workflow->id,
            'module' => $workflow->module,
            'role' => 'rejected',
            'stage_order' => $workflow->current_stage_order,
            'entered_at' => now(),
        ]);

        Log::warning("Workflow REJECTED: {$workflow->id}");
    }
}