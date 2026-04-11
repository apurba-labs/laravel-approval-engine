<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;

class CompleteWorkflowAction
{
    public function execute($workflow)
    {
        $workflow->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        WorkflowLog::create([
            'workflow_instance_id' => $workflow->id,
            'module' => $workflow->module,
            'role' => 'completed',
            'stage_order' => $workflow->current_stage_order,
            'entered_at' => now(),
        ]);

        $engine = app(WorkflowEngine::class);
        $module = $engine->getModule($workflow->module);

        $module->query()
            ->whereKey($workflow->source_id)
            ->update([
                'status' => 'fully_approved',
                'stage_status' => 'finished',
            ]);

        event(new WorkflowCompleted($workflow));

        return $workflow->fresh();
    }
}