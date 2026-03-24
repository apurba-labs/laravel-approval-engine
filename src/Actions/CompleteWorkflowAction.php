<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;

class CompleteWorkflowAction
{
    public function execute($batch)
    {
        $batch->update([
            'status'=>'completed',
            'completed_at'=>now()
        ]);

        // Update the Source Records (e.g., Requisitions)
        $engine = app(WorkflowEngine::class);
        $module = $engine->getModule($batch->module);
        $module->query()
            ->whereBetween($module->approvedColumn(), [$batch->window_start, $batch->window_end])
            ->update([
                'status' => 'fully_approved',
                'stage_status' => 'finished'
            ]);

        event(new WorkflowCompleted($batch));

        return $batch;
    }
}
