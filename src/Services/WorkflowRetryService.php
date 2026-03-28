<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

class WorkflowRetryService
{
    public function scheduleRetry(WorkflowNotification $notification): void
    {
        $retryCount = $notification->retry_count + 1;

        // simple backoff (can improve later)
        $delay = now()->addMinutes(5 * $retryCount);

        $notification->update([
            'retry_count' => $retryCount,
            'next_retry_at' => $delay,
            'status' => 'failed',
        ]);
    }
}