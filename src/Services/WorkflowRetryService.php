<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;

class WorkflowRetryService
{
    public function scheduleRetry(WorkflowNotification $notification): void
    {
        $retryCount = $notification->retry_count + 1;

        if ($retryCount >= $notification->max_retries) {
            $notification->update([
                'retry_count' => $retryCount,
                'next_retry_at' => null,
                'status' => 'permanent_failed',
            ]);

            return;
        }

        $delayMinutes = $this->calculateBackoff($retryCount);

        $notification->update([
            'retry_count' => $retryCount,
            'next_retry_at' => now()->addMinutes($delayMinutes),
            'status' => 'failed',
        ]);
    }

    protected function calculateBackoff(int $retryCount): int
    {
        return match ($retryCount) {
            1 => 5,
            2 => 15,
            3 => 30,
            default => 60,
        };
    }
}