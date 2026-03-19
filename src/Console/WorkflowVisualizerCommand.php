<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

class WorkflowVisualizerCommand extends Command
{
    protected $signature = 'approval:workflow {module}';

    protected $description = 'Show workflow stages for a module';

    public function handle()
    {
        $module = $this->argument('module');

        $stages = WorkflowStage::where('module', $module)
            ->orderBy('stage_order')
            ->get();

        if ($stages->isEmpty()) {
            $this->error("No workflow stages found.");
            return;
        }

        $this->info("Workflow: {$module}");
        $this->line("");

        foreach ($stages as $stage) {

            $this->line(
                "Stage {$stage->stage_order} → {$stage->role}"
            );

        }

        $this->line("");
        $this->info("Total stages: ".$stages->count());
    }
}
