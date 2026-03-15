<?php
namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Support\Str;

class BatchProcessor
{
    public function createBatch($module, $stage, $start, $end)
    {
        return WorkflowBatch::create([
            'module' => $module,
            'stage' => $stage,
            'token' => Str::random(32),
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
    }
}
