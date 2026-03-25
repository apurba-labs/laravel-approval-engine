<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Support\BatchWindowResolver;
use Illuminate\Support\Str;

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

        if($nextStage){
            //Try to find the specific setting for the next role
            $setting = WorkflowSetting::where('module', $batch->module)
                ->where('role', $nextStage->role)
                ->where('is_active', true)
                ->first();

            //Fallback: If no setting exists, create a default 24h window
            if ($setting) {
                $window = $windowResolver->resolve($setting);
                $start = $window['start'];
                $end = $window['end'];
            } else {
                $start = now();
                $end = now()->addDay();
            }

            $window = $windowResolver->resolve($setting);
            $start = $window['start'] ?? now();
            $end = $window['end'] ?? now()->addHours(24);

            return WorkflowBatch::create([
                'module'       => $batch->module,
                'role'         => $nextStage->role,
                'token'        => WorkflowBatch::generateToken(),
                'status'       => 'pending',
                'window_start' => $start,
                'window_end'   => $end
            ]);
        }

        // No more stages? Complete the workflow.
        return app(CompleteWorkflowAction::class)->execute($batch);
    }
}
