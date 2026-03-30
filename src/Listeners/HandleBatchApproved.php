<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use Illuminate\Support\Facades\Log;

class HandleBatchApproved
{
    public function handle(BatchApproved $event)
    {
        $batch = $event->batch;

        // TIMELINE LOG
        WorkflowLog::create([
            'workflow_instance_id' => $batch->workflow_instance_id,
            'module' => $batch->module,
            'role' => 'approved',
            'stage_order' => $batch->stage,
            'entered_at' => now(),
        ]);

        $notifications = WorkflowNotification::where('module', $batch->module)
            ->where('role', $batch->role)
            ->whereBetween('created_at', [
                $batch->window_start,
                $batch->window_end
            ])
            ->where('status', 'sent')
            ->get();

        if ($notifications->isEmpty()) {
            Log::info("No notifications found for batch {$batch->id}");
            return;
        }

        app(NotificationService::class)
            ->sendBatch($batch, $notifications);
    }
}