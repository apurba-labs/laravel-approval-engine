<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\IAM\Facades\IAM;

class ApproveBatchAction
{
    public function execute(string $token, int $userId)
    {
        $batch = WorkflowBatch::where('token', $token)->firstOrFail();

        $this->authorizeApprover($batch, $userId);

        $notifications = WorkflowNotification::query()
            ->where('batch_id', $batch->id)
            ->get();

        foreach ($notifications as $notification) {
            $this->approveNotification($notification, $userId);
        }

        $batch->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $batch->fresh();
    }

    protected function authorizeApprover(WorkflowBatch $batch, int $userId): void
    {
        $userModel = config('auth.providers.users.model');
        $user = $userModel::findOrFail($userId);

        $authorized = match ($batch->assign_type) {
            'permission' => IAM::can(
                $user,
                $batch->assign_value
            ),

            'role' => method_exists($user, 'hasRole')
                ? $user->hasRole($batch->assign_value)
                : false,

            'user' => $user->id == $batch->assign_value,

            default => false,
        };

        abort_unless(
            $authorized,
            403,
            'Unauthorized to approve this batch.'
        );
    }

    protected function approveNotification(
        WorkflowNotification $notification,
        int $userId
    ): void {
        WorkflowApproval::create([
            'workflow_instance_id' => $notification->workflow_instance_id,
            'batch_id' => $notification->batch_id,
            'user_id' => $userId,
            'stage' => $notification->stage_order,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $workflow = $notification->workflowInstance;

        app(MoveToNextStageAction::class)
            ->execute($workflow, $notification->stage_order);
    }
}