<?php

namespace ApurbaLabs\ApprovalEngine\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use ApurbaLabs\ApprovalEngine\Contracts\NotificationInterface;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowNotification;

class DefaultNotification implements NotificationInterface
{
    public function createNotification($workflow, $stage, $recipient)
    {
        // minimal fallback (no DB creation)
        Log::info('DefaultNotification:createNotification called', [
            'workflow_id' => $workflow->id ?? null,
        ]);

        return null;
    }

    public function dispatch(WorkflowNotification $notification): void
    {
        $this->log($notification, 'dispatch');
    }

    public function sendSingle(WorkflowNotification $notification): void
    {
        $this->log($notification, 'sendSingle');
    }

    public function sendBatch($batch, Collection $notifications): void
    {
        Log::info('DefaultNotification:sendBatch', [
            'batch_id' => $batch->id ?? null,
            'count' => $notifications->count(),
        ]);
    }

    public function sendImmediateIfNeeded(WorkflowNotification $notification): void
    {
        $this->log($notification, 'sendImmediateIfNeeded');
    }

    public function dispatchAndMaybeSend(WorkflowNotification $notification): void
    {
        $this->dispatch($notification);
        $this->sendImmediateIfNeeded($notification);
    }

    protected function log(WorkflowNotification $notification, string $action): void
    {
        Log::info("DefaultNotification:{$action}", [
            'notification_id' => $notification->id,
            'workflow_id' => $notification->workflow_instance_id,
        ]);
    }
}