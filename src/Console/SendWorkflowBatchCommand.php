<?php
namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Engine\BatchProcessor;

class SendWorkflowBatchCommand extends Command
{
    protected $signature = 'approval:send-batch';

    protected $description = 'Process workflow approval batches';

    public function handle()
    {
        $engine = app(WorkflowEngine::class);
        $processor = app(BatchProcessor::class);

        $modulesConfig = config('approval-engine.modules', []);
        
        if (empty($modulesConfig)) {
            $this->warn('No workflow modules found in config/approval-engine.php');
            return Command::SUCCESS;
        }

        $modules = array_keys($modulesConfig);

        foreach ($modules as $module) {
            $this->info("Processing module: {$module}");

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

            $this->info("Batch created for module: {$module}");
        }
    }
}
