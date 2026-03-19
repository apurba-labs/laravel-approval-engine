<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;

class SendBatchApprovalNotification
{
    public function handle(WorkflowCompleted $event)
    {
        // Logic to notify the original creator of the requisitions
        // For now, let's just log it or send to admin
        \Log::info("Workflow for {$event->batch->module} (Batch #{$event->batch->id}) is now FULLY APPROVED.");
    }
}
