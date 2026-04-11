<?php

namespace ApurbaLabs\ApprovalEngine\Support;

use Illuminate\Support\Str;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Events\BatchSent;

class BatchProcessor
{
    public function createBatch(
        string $module,
        string $recipientSignature,
        $start,
        $end,
        ?string $role = null,
        ?string $assignType = null,
        ?string $assignValue = null
    ): WorkflowBatch {
        return WorkflowBatch::create([
            'module' => $module,

            // Legacy
            'role' => $role,

            // Snapshot
            'assign_type' => $assignType,
            'assign_value' => $assignValue,
            'recipient_signature' => $recipientSignature,

            'token' => WorkflowBatch::generateToken(),
            'window_start' => $start,
            'window_end' => $end,
            'status' => 'pending',
            'item_count' => 0,
        ]);
    }

    public function markSent(WorkflowBatch $batch, int $count): void
    {
        $batch->update([
            'item_count' => $count,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        event(new BatchSent($batch));
    }

    public function markFailed(WorkflowBatch $batch, string $error = null): void
    {
        $batch->update([
            'status' => 'failed',
        ]);

        \Log::error("Batch {$batch->id} failed: " . $error);
    }

    public function findExistingBatch(
        string $module,
        string $recipientSignature,
        $start,
        $end
    ) {
        return WorkflowBatch::where([
            'module' => $module,
            'recipient_signature' => $recipientSignature,
            'window_start' => $start,
            'window_end' => $end,
        ])->first();
    }
}