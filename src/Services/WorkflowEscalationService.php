<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

class WorkflowEscalationService
{
    public function escalate(WorkflowNotification $notification): void
    {
        if (!$notification->escalate_to) {
            return;
        }

        // create new notification for escalation
        app(NotificationService::class)->createNotification(
            $notification->workflowInstance,
            $notification->escalate_to,
            null // recipient will be resolved later
        );
    }
}