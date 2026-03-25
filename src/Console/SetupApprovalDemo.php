<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;

use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;

class SetupApprovalDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:setup-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup demo data for Approval Engine';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up demo data...');

        // Clear existing
        WorkflowStage::truncate();
        WorkflowRule::truncate();

        // --- STAGES ---
        WorkflowStage::create([
            'module' => 'purchase',
            'stage_order' => 1,
            'role' => 'HOSD',
        ]);

        WorkflowStage::create([
            'module' => 'requisition',
            'stage_order' => 1,
            'role' => 'Manager',
        ]);

        // --- RULES ---
        WorkflowRule::create([
            'module' => 'purchase',
            'field' => 'total_amount',
            'operator' => '>',
            'value' => '5000',
            'role' => 'COO',
            'priority' => 10,
        ]);

        $this->info('Demo setup complete!');
    }
}
