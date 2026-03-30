<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Support\BatchWindowResolver;

class MoveToNextStageAction
{
    public function execute($batch, int $stage)
    {
        $windowResolver = app(BatchWindowResolver::class);
        $stageNavigator = app(StageNavigator::class);

        $nextStage = $stageNavigator->getNextStage(
            $batch->module,
            $batch->stage
        );

        if ($nextStage) {

            $setting = WorkflowSetting::where('module', $batch->module)
                ->where('role', $nextStage->role)
                ->where('is_active', true)
                ->first();

            // Clean window logic
            if ($setting) {
                $window = $windowResolver->resolve($setting);
                $start = $window['start'];
                $end = $window['end'];
            } else {
                $start = now();
                $end = now()->addHours(24);
            }

            // Create next batch
            $newBatch = WorkflowBatch::create([
                'module'       => $batch->module,
                'role'         => $nextStage->role,
                'token'        => WorkflowBatch::generateToken(),
                'status'       => 'pending',
                'window_start' => $start,
                'window_end'   => $end
            ]);

            // Aadding LOG HERE
            WorkflowLog::create([
                'workflow_instance_id' => $batch->workflow_instance_id,
                'module' => $batch->module,
                'role' => $nextStage->role,
                'stage_order' => $nextStage->stage_order,
                'entered_at' => now(),
            ]);

            return $newBatch;
        }

        // log completion
        WorkflowLog::create([
            'workflow_instance_id' => $batch->workflow_instance_id,
            'module' => $batch->module,
            'role' => 'completed',
            'stage_order' => $batch->stage,
            'entered_at' => now(),
        ]);

        return app(CompleteWorkflowAction::class)->execute($batch);
    }
}