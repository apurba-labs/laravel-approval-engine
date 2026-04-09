<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification;
use ApurbaLabs\ApprovalEngine\Notifications\WorkflowSingleNotification;

/**
 * Legacy methods (v1.3)
 * These will be refactored or removed in future versions.
 * New flow uses: createNotification() + dispatch()
 */
class NotificationService
{
    public function createNotification(
        $workflow,
        $stage,
        $recipient,
        ?string $assignType = null,
        ?string $assignValue = null
    ): WorkflowNotification {
        $assignType ??= $stage->resolved_assign_type
            ?? $stage->assign_type
            ?? 'role';

        $assignValue ??= $stage->resolved_assign_value
            ?? $stage->assign_value
            ?? $stage->role;

        return WorkflowNotification::create([
            'workflow_instance_id' => $workflow->id,
            'module' => $workflow->module,

            // Legacy / display
            'role' => $stage->role,

            // Stage snapshot
            'stage_id' => $stage->id,
            'stage_order' => $stage->stage_order,

            // Assignment snapshot
            'assign_type' => $assignType,
            'assign_value' => $assignValue,

            // Deterministic grouping/auth key
            'recipient_signature' => $this->buildRecipientSignature(
                $assignType,
                $assignValue
            ),

            // Intended recipient
            'recipient_id' => $recipient?->id,
            'recipient_type' => $recipient ? get_class($recipient) : null,

            // Actual resolved recipient
            'resolved_recipient_id' => $recipient?->id,
            'resolved_recipient_type' => $recipient ? get_class($recipient) : null,

            'status' => 'pending',
        ]);
    }

    protected function buildRecipientSignature(string $assignType, string $assignValue, $scopeId = null): string 
    {
        return collect([
            $assignType,
            $assignValue,
            $scopeId ? "scope:{$scopeId}" : null,
        ])->filter()->implode(':');
    }

    public function dispatch(WorkflowNotification $notification): void
    {
        app(WorkflowNotificationDispatcher::class)
            ->dispatch($notification);
    }

    /**
     * Send immediately if setting is 'instant'
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
     * Send a single notification (instant mode)
     */
    public function sendSingle(WorkflowNotification $notification): void
    {
        try {
            $recipient = $notification->recipient;

            if (!$recipient) {
                Log::warning("No recipient for notification {$notification->id}");
                return;
            }

            Notification::send(
                $recipient,
                new WorkflowSingleNotification($notification)
            );

            $notification->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Throwable $e) {
            $this->markFailed($notification, $e);
        }
    }

    /**
     * Send batch notifications (grouped)
     */
    public function sendBatch($batch, Collection $notifications): void
    {
        // recipients (models, not emails)
        $recipients = $this->resolveBatchRecipients($notifications);

        if ($recipients->isEmpty()) {
            Log::warning("Batch {$batch->id} has no recipients.");
            return;
        }

        try {
            Notification::send(
                $recipients,
                new WorkflowBatchNotification($batch, $notifications)
            );

            // mark all as sent
            WorkflowNotification::whereIn('id', $notifications->pluck('id'))
                ->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);

        } catch (\Throwable $e) {

            // mark all as failed
            WorkflowNotification::whereIn('id', $notifications->pluck('id'))
                ->update([
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ]);

            Log::error("Batch send failed: " . $e->getMessage());
        }
    }

    /**
     * Resolve recipients from notifications (polymorphic)
     */
    protected function resolveBatchRecipients(Collection $notifications): Collection
    {
        return $notifications
            ->map(fn ($n) => $n->recipient)   // MorphTo relation
            ->filter()                       // remove null
            ->unique(fn ($model) => get_class($model) . ':' . $model->getKey())
            ->values();
    }

    /**
     * Fetch setting (used for instant logic)
     */
    protected function getSetting(WorkflowNotification $notification)
    {
        return \ApurbaLabs\ApprovalEngine\Models\WorkflowSetting::where('module', $notification->module)
            ->where('role', $notification->role)
            ->first();
    }

    /**
     * Mark single notification failed
     */
    protected function markFailed(WorkflowNotification $notification, \Throwable $e): void
    {
        $notification->update([
            'status' => 'failed',
            'error'  => $e->getMessage(),
        ]);

        Log::error("Notification {$notification->id} failed: " . $e->getMessage());
    }
}