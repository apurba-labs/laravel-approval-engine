<?php
namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Support\StageResolver;
use ApurbaLabs\ApprovalEngine\Support\BatchProcessor;

class SendWorkflowBatchCommand extends Command
{
    protected $signature = 'approval:send-batch';

    protected $description = 'Process workflow approval batches';

    public function handle()
    {
        $engine = app(WorkflowEngine::class);
        $processor = app(BatchProcessor::class);

        $modules = $engine->discoverModules();

        if (empty($modules)) {
            $this->warn('No workflow modules discovered in ' . config('approval-engine.modules_path'));
            return Command::SUCCESS;
        }

        foreach ($modules as $module) {
            $moduleName = $module->name(); 
            $this->info("Processing module: {$moduleName}");

            $records = $engine->getApprovedRecords(
                $moduleName,
                now()->subDay(),
                now()
            );

            if ($records->isEmpty()) {
                continue;
            }
            dump("Found " . $records->count() . " records for " . $module->name());
            $batch = $processor->createBatch(
                $moduleName,
                1,
                now()->subDay(),
                now()
            );

            $processor->markSent($batch, $records->count());

            $this->info("Batch created for module: {$moduleName}");
        }
    }
}
