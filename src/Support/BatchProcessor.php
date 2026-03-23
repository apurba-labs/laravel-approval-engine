<?php
namespace ApurbaLabs\ApprovalEngine\Support;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Support\Str;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;

class BatchProcessor
{
    public function createBatch(string $module, string $role, ?int $stage, $start, $end)
    {
        $stage = $stage ?? 1;

        $moduleName = is_string($module) ? $module : $module->name();

        return WorkflowBatch::create([
            'module' => $moduleName,
            'role' => $role,
            'stage' => $stage,
            'token' => WorkflowBatch::generateToken(),
            'window_start' => $start,
            'window_end' => $end,
            'status' => 'pending'
        ]);
    }

    public function markSent($batch, $count)
    {
        $batch->update([
            'item_count' => $count,
            'sent_at' => now(),
            'status' => 'sent'
        ]);

        event(new BatchApproved($batch));
    }
}
