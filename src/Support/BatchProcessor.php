<?php
namespace ApurbaLabs\ApprovalEngine\Support;

use Illuminate\Support\Str;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;

class BatchProcessor
{
    /**
     * Create a new batch
     */
    public function createBatch(
        string $module,
        string $role,
        $start,
        $end
    ): WorkflowBatch {

        return WorkflowBatch::create([
            'module' => $module,
            'role' => $role,
            'token' => Str::uuid(),
            'window_start' => $start,
            'window_end' => $end,
            'status' => 'pending',
            'item_count' => 0,
        ]);
    }

    /**
     * Mark batch as sent
     */
    public function markSent(WorkflowBatch $batch, int $count): void
    {
        $batch->update([
            'item_count' => $count,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        event(new BatchApproved($batch));
    }

    /**
     * Mark batch as failed
     */
    public function markFailed(WorkflowBatch $batch, string $error = null): void
    {
        $batch->update([
            'status' => 'failed',
        ]);

        \Log::error("Batch {$batch->id} failed: " . $error);
    }

    public function findExistingBatch($module, $role, $start, $end)
    {
        return WorkflowBatch::where([
            'module' => $module,
            'role' => $role,
            'window_start' => $start,
            'window_end' => $end,
        ])->first();
    }
}