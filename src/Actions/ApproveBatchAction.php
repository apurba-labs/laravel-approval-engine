<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\IAM\Facades\IAM;

class ApproveBatchAction
{
    public function __construct(
        protected WorkflowEngine $engine
    ) {}

    public function execute(string $token, int $userId)
    {
        $batch = WorkflowBatch::where('token', $token)->firstOrFail();

        $this->authorizeApprover($batch, $userId);

        $notifications = WorkflowNotification::where('batch_id', $batch->id)->get();

        foreach ($notifications as $notification) {

            $workflow = $notification->workflowInstance;

            // MOVE TO ENGINE
            $this->engine->approve($workflow, $userId);

            $notification->update([
                'status' => 'approved',
            ]);
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
            'permission' => IAM::can($user, $batch->assign_value),
            'role' => method_exists($user, 'hasRole')
                ? $user->hasRole($batch->assign_value)
                : false,
            'user' => $user->id == $batch->assign_value,
            default => false,
        };

        abort_unless($authorized, 403, 'Unauthorized to approve this batch.');
    }
}