<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

class HandleBatchApproved
{
    public function handle(BatchApproved $event)
    {
        $batch = $event->batch;

        // Get notifications linked to this batch window
        $notifications = WorkflowNotification::where('module', $batch->module)
            ->where('role', $batch->role)
            ->whereBetween('created_at', [
                $batch->window_start,
                $batch->window_end
            ])
            ->where('status', 'sent') // already processed
            ->get();

        if ($notifications->isEmpty()) {
            \Log::info("No notifications found for batch {$batch->id}");
            return;
        }

        // Delegate to NotificationService
        app(NotificationService::class)
            ->sendBatch($batch, $notifications);
    }
}
