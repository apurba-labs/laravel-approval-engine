<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Contracts\NotificationInterface;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowSingleNotification;
use ApurbaLabs\ApprovalEngine\Services\WorkflowNotificationDispatcher;

class NotificationService implements NotificationInterface
{
    /**
     * Create notification record
     */
    public function createNotification(
        $workflow,
        $stage,
        $recipient,
        ?string $assignType = null,
        ?string $assignValue = null
    ): WorkflowNotification {

        [$assignType, $assignValue] = $this->resolveAssignment(
            $stage,
            $assignType,
            $assignValue
        );

        return WorkflowNotification::create([
            'workflow_instance_id' => $workflow->id,
            'module' => $workflow->module,

            'role' => $stage->role,

            'stage_id' => $stage->id,
            'stage_order' => $stage->stage_order,

            'assign_type' => $assignType,
            'assign_value' => $assignValue,

            'recipient_signature' => $this->buildRecipientSignature(
                $assignType,
                $assignValue
            ),

            'recipient_id' => $recipient?->id,
            'recipient_type' => $recipient ? get_class($recipient) : null,

            'resolved_recipient_id' => $recipient?->id,
            'resolved_recipient_type' => $recipient ? get_class($recipient) : null,

            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => 3,
            'escalate_at' => now()->addHours(24),
            'escalate_assign_type' => $assignType,
            'escalate_assign_value' => $assignValue,
        ]);
    }

    /**
     * Dispatch notification (queue or handler)
     */
    public function dispatch(WorkflowNotification $notification): void
    {
        app(WorkflowNotificationDispatcher::class)->dispatch($notification);
    }

    /**
     * Send immediately if needed
     */
    public function sendImmediateIfNeeded(WorkflowNotification $notification): void
    {
        $setting = $this->getSetting($notification);

        if (!$setting || $setting->frequency !== 'instant') {
            return;
        }

        $this->sendSingle($notification);
    }

    /**
     * Send single notification
     */
    public function sendSingle(WorkflowNotification $notification): void
    {
        try {
            $recipient = $notification->recipient;

            if (!$recipient) {
                Log::warning("No recipient for notification", [
                    'notification_id' => $notification->id,
                    'workflow_id' => $notification->workflow_instance_id,
                ]);
                return;
            }

            $this->sendViaLaravel(
                $recipient,
                new WorkflowSingleNotification($notification)
            );

            $this->markSent($notification);

        } catch (\Throwable $e) {
            $this->markFailed($notification, $e);
        }
    }

    /**
     * Send batch notifications
     */
    public function sendBatch($batch, Collection $notifications): void
    {
        $recipients = $this->resolveBatchRecipients($notifications);

        if ($recipients->isEmpty()) {
            Log::warning("Batch {$batch->id} has no recipients.");
            return;
        }

        try {
            $this->sendViaLaravel(
                $recipients,
                new WorkflowBatchNotification($batch, $notifications)
            );

            $this->markBatchSent($notifications);

        } catch (\Throwable $e) {
            $this->markBatchFailed($notifications, $e);

            Log::error("Batch send failed", [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Laravel Notification wrapper (future plugin point)
     */
    protected function sendViaLaravel($recipient, $notification): void
    {
        Notification::send($recipient, $notification);
    }

    /**
     * Mark single notification sent
     */
    protected function markSent(WorkflowNotification $notification): void
    {
        $notification->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark batch sent
     */
    protected function markBatchSent(Collection $notifications): void
    {
        $notifications->chunk(100)->each(function ($chunk) {
            WorkflowNotification::whereIn('id', $chunk->pluck('id'))
                ->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);
        });
    }

    /**
     * Mark batch failed
     */
    protected function markBatchFailed(Collection $notifications, \Throwable $e): void
    {
        $notifications->chunk(100)->each(function ($chunk) use ($e) {
            WorkflowNotification::whereIn('id', $chunk->pluck('id'))
                ->update([
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ]);
        });
    }

    /**
     * Mark single failed with retry support
     */
    protected function markFailed(WorkflowNotification $notification, \Throwable $e): void
    {
        $notification->increment('retry_count');

        $notification->update([
            'status' => 'failed',
            'error'  => $e->getMessage(),
        ]);

        Log::error("Notification failed", [
            'notification_id' => $notification->id,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Resolve assignment logic
     */
    protected function resolveAssignment($stage, $assignType, $assignValue): array
    {
        return [
            $assignType ?? $stage->resolved_assign_type ?? $stage->assign_type ?? 'role',
            $assignValue ?? $stage->resolved_assign_value ?? $stage->assign_value ?? $stage->role,
        ];
    }

    /**
     * Build recipient signature
     */
    protected function buildRecipientSignature(string $assignType, string $assignValue, $scopeId = null): string
    {
        return collect([
            $assignType,
            $assignValue,
            $scopeId ? "scope:{$scopeId}" : null,
        ])->filter()->implode(':');
    }

    /**
     * Resolve recipients for batch
     */
    protected function resolveBatchRecipients(Collection $notifications): Collection
    {
        return $notifications
            ->map(fn ($n) => $n->recipient)
            ->filter()
            ->unique(fn ($model) => get_class($model) . ':' . $model->getKey())
            ->values();
    }

    /**
     * Get notification setting (cached)
     */
    protected function getSetting(WorkflowNotification $notification)
    {
        return cache()->remember(
            "workflow_setting:{$notification->module}:{$notification->role}",
            now()->addMinutes(10),
            fn() => \ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowSetting::where('module', $notification->module)
                ->where('role', $notification->role)
                ->first()
        );
    }

    /**
     * Helper: dispatch + immediate send
     */
    public function dispatchAndMaybeSend(WorkflowNotification $notification): void
    {
        $this->dispatch($notification);
        $this->sendImmediateIfNeeded($notification);
    }
}