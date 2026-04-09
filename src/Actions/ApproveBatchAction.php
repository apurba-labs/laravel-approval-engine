<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Actions\MoveToNextStageAction;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;

class ApproveBatchAction
{
    public function execute(string $token, int $userId)
    {
        $batch = WorkflowBatch::where('token',$token)->firstOrFail();

        $recipientResolver = app(WorkflowRecipientResolver::class);
        $stageNavigator = app(StageNavigator::class);

        $currentStage = $stageNavigator->getCurrentStage(
            $batch->module,
            $batch->stage
        );

        $authorizedRecipient = $recipientResolver->resolve($currentStage, $batch);

        abort_unless(
            $authorizedRecipient && $authorizedRecipient->id === $userId,
            403,
            'Unauthorized to approve this batch.'
        );

        WorkflowApproval::create([
            'batch_id'=>$batch->id,
            'user_id'=>$userId,
            'stage'=>$batch->stage,
            'status'=>'approved',
            'approved_at'=>now()
        ]);

        event(new BatchApproved($batch));

        $nextStage = $stageNavigator->getNextStage(
            $batch->module,
            $batch->stage
        );

        if($nextStage){

            app(MoveToNextStageAction::class)
                ->execute($batch, $nextStage->stage_order);

        }else{

            app(CompleteWorkflowAction::class)
                ->execute($batch);
        }

        return $batch;
    }
}
