<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use Illuminate\Support\Facades\Log;

class HandleWorkflowCompleted
{
    public function handle(WorkflowCompleted $event)
    {
        $batch = $event->batch;

        // TIMELINE LOG ONLY
        WorkflowLog::create([
            'workflow_instance_id' => $batch->workflow_instance_id,
            'module' => $batch->module,
            'role' => 'completed',
            'stage_order' => $batch->stage,
            'entered_at' => now(),
        ]);

        Log::info("Workflow COMPLETED: Batch #{$batch->id}");
    }
}