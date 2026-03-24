<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requisition;
use App\Models\User; 
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Support\BatchProcessor;
use ApurbaLabs\ApprovalEngine\Enums\WorkflowStatus;
use Carbon\Carbon;

class ApprovalDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run approval engine demo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Running Approval Engine Demo...");

        $now = Carbon::now();

        $user1 = User::updateOrCreate(
            ['email' => 'apurbansingh@yahoo.com'],
            ['name' => 'Apurba', 'password' => bcrypt('password')]
        );

        Requisition::create([
            'user_id' => $user1->id,
            'reference_id' => 'REQ-001',
            'stage' => 1,
            'status' => WorkflowStatus::APPROVED->value,
            'stage_status' => WorkflowStatus::APPROVED->value,
            'approved_at' => $now->copy()->subHours(2),
            'created_at' => $now->copy()->subDay(),
        ]);

        $this->info("Sample requisitions created");

        $engine = app(WorkflowEngine::class);
        $records = $engine->getApprovedRecords(
            'requisition',
            now()->subDay(),
            now()
        );

        $this->info("Approved records fetched: ".$records->count());

        $processor = app(BatchProcessor::class);
        
        $batch = $processor->createBatch(
            'requisition', 
            'HOSD', 
            now()->subDay(), 
            now() 
        );

        $this->info("Batch created with token: {$batch->token}");
        $this->line("");
        $this->info("Approval Link: " . url("/approval/batch/{$batch->token}"));
        $this->info("Demo Completed!");
    }
}
