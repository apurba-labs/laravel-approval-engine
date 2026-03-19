<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;

class BatchStatusCommand extends Command
{
    protected $signature = 'approval:status';

    protected $description = 'Show pending approval batches';

    public function handle()
    {
        $batches = WorkflowBatch::where('status','pending')->get();

        if ($batches->isEmpty()) {

            $this->info("No pending batches.");

            return;
        }

        $rows = [];

        foreach ($batches as $batch) {

            $rows[] = [
                $batch->module,
                $batch->stage,
                $batch->status
            ];
        }

        $this->table(
            ['Module','Stage','Status'],
            $rows
        );
    }
}
