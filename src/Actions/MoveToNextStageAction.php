<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;

class MoveToNextStageAction
{
    public function execute($workflow, int $currentStageOrder)
    {
        $stageNavigator = app(StageNavigator::class);

        $nextStage = $stageNavigator->getNextStage(
            $workflow->module,
            $currentStageOrder
        );

        if (!$nextStage) {
            return app(CompleteWorkflowAction::class)
                ->execute($workflow);
        }

        $recipient = app(WorkflowRecipientResolver::class)
            ->resolve($nextStage, $workflow);

        if (!$recipient) {
            throw new \RuntimeException(
                "No recipient resolved for next stage [{$nextStage->id}]"
            );
        }

        app(NotificationService::class)
            ->createNotification(
                workflow: $workflow,
                stage: $nextStage,
                recipient: $recipient
            );

        WorkflowLog::create([
            'workflow_instance_id' => $workflow->id,
            'module' => $workflow->module,
            'role' => $nextStage->role,
            'stage_order' => $nextStage->stage_order,
            'entered_at' => now(),
        ]);

        $workflow->update([
            'current_stage_order' => $nextStage->stage_order,
            'status' => 'in_progress',
        ]);

        return $workflow->fresh();
    }
}