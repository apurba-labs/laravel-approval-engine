<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;

class WorkflowMetricsService
{
    public function averageApprovalTime(): float
    {
        $approvals = WorkflowApproval::whereNotNull('completed_at')
            ->whereNotNull('assigned_at')
            ->get();

        if ($approvals->isEmpty()) {
            return 0;
        }

        return $approvals->avg(function ($approval) {
            return $approval->completed_at->diffInSeconds($approval->assigned_at, true);
        });
    }

    public function slaBreachCount(): int
    {
        return WorkflowApproval::whereNotNull('due_at')
            ->whereNotNull('completed_at')
            ->whereColumn('completed_at', '>', 'due_at')
            ->count();
    }

    public function pendingApprovals(): int
    {
        return WorkflowApproval::where('status', 'pending')->count();
    }
}