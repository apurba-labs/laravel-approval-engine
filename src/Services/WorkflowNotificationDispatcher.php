<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowNotification;

class WorkflowNotificationDispatcher
{
    public function dispatch(WorkflowNotification $notification): void
    {
        dispatch(new \ApurbaLabs\ApprovalEngine\Jobs\SendWorkflowNotificationJob($notification));
    }
}