<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Contracts\NotificationInterface;
use ApurbaLabs\ApprovalEngine\Events\WorkflowRejected;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowLog;
use Illuminate\Support\Facades\Log;

class HandleWorkflowRejected
{
    public function handle(WorkflowRejected $event): void
    {
        try {
            $workflow = $event->workflow;

            // Already logged in engine (optional double log guard)

            // Notify requester / creator
            $notificationService = app(NotificationInterface::class);

            $notification = $notificationService->createNotification(
                workflow: $workflow,
                stage: null,
                recipient: $workflow->created_by ?? null // adjust if needed
            );

            if ($notification) {
                $notificationService->dispatchAndMaybeSend($notification);
            }

            \Log::warning("Workflow REJECTED: {$workflow->id}");

        } catch (\Throwable $e) {
            \Log::error('Error handling WorkflowRejected', [
                'error' => $e->getMessage(),
                'workflow_id' => $event->workflow->id ?? null,
            ]);
        }
    }
}