<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

class WorkflowNotificationDispatcher
{
    public function dispatch(WorkflowNotification $notification): void
    {
        dispatch(new \ApurbaLabs\ApprovalEngine\Jobs\SendWorkflowNotificationJob($notification));
    }
}