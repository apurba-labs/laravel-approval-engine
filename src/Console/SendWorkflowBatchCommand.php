<?php
namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

class SendWorkflowBatchCommand extends Command
{
    protected $signature = 'approval:send-batch';

    protected $description = 'Process workflow approval batches';

    public function handle()
    {
        $engine = app(WorkflowEngine::class);

        $processor = app(BatchProcessor::class);

        $modules = array_keys(config('approval-engine.modules'));

        foreach ($modules as $module) {

            $records = $engine->getApprovedRecords(
                $module,
                now()->subDay(),
                now()
            );

            if ($records->isEmpty()) {
                continue;
            }

            $batch = $processor->createBatch(
                $module,
                1,
                now()->subDay(),
                now()
            );

            $processor->markSent($batch, $records->count());

            $this->info("Batch created for module: $module");
        }
    }
}
