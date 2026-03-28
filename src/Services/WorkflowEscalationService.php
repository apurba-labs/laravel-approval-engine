<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;

class WorkflowEscalationService
{
    public function processEscalations(): void
    {
        WorkflowApproval::where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->each(function ($approval) {

                // Escalate to fallback role (simple version)
                app(NotificationService::class)->createNotification(
                    $approval->workflowInstance,
                    'admin', // later dynamic
                    null
                );
            });
    }
}