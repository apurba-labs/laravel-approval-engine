<?php
namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

use ApurbaLabs\ApprovalEngine\Support\StageResolver;
use ApurbaLabs\ApprovalEngine\Support\BatchProcessor;
use ApurbaLabs\ApprovalEngine\Support\BatchWindowResolver;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;

class SendWorkflowBatchCommand extends Command
{
    protected $signature = 'approval:send-batch {--force : Ignore last_run_at and send anyway}';

    protected $description = 'Process workflow approval batches';

    public function handle()
    {
        $engine = app(WorkflowEngine::class);
        $processor = app(BatchProcessor::class);
        $stageResolver = app(StageResolver::class);
        $windowResolver = app(BatchWindowResolver::class);

        $modules = $engine->discoverModules();
        if (empty($modules)) {
            $this->warn('No workflow modules discovered in ' . config('approval-engine.modules_path'));
            return Command::SUCCESS;
        }

        foreach ($modules as $module) {
            $moduleName = $module->name(); 
            $this->info("Processing module: {$moduleName}");

            $settingsList = WorkflowSetting::where('module', $moduleName)
                ->where('is_active', true)
                ->get();

            foreach ($settingsList as $settings) {
                $shouldSendToday = $this->shouldSendToday($settings);
                
                if (!$shouldSendToday) continue;

                $settings->update(['last_run_at' => now()]);

                $this->info("Processing module: {$moduleName} for Role: {$settings->role}");

                $window = $windowResolver->resolve($settings);
                $start = $window['start'] ?? now()->subDay();
                $end = $window['end'] ?? now();

                DB::enableQueryLog();
                $records = $engine->getApprovedRecords(
                    $moduleName,
                    $start,
                    $end
                );

                if ($records->isEmpty()) {
                    $this->line(" - No records found for this window.");
                    continue;
                }

                $stage = $stageResolver->getStage($moduleName, $settings->role);

                try {
                    $batch = $processor->createBatch(
                        $moduleName,
                        $settings->role,
                        $stage,
                        $start,
                        $end
                    );
                    $processor->markSent($batch, $records->count());

                } catch (\Exception $e) {
                    dump("BATCH CREATION FAILED: " . $e->getMessage());
                }
                $this->info("Batch created for module: {$moduleName}");
            }
        }
    }

    private function shouldSendToday($setting)
    {
        if ($this->option('force')) return true;

        $timezone = $setting->timezone ?? config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);

        if ($setting->last_run_at) {
            $lastRun = Carbon::parse($setting->last_run_at)->timezone($timezone);
            if ($lastRun->isToday()) {
                return false; 
            }
        }

        $currentTime = $now->format('H:i');
        $scheduledTime = Carbon::parse($setting->send_time)->format('H:i');
        if ($currentTime < $scheduledTime) { 
            return false; // not yet time
        }

        return match ($setting->frequency) {
            'daily'   => true,
            'weekly'  => (int) $now->dayOfWeek === (int) $setting->weekly_day,
            'monthly' => (int) $now->day === (int) $setting->monthly_date,
            default   => false,
        };
    }
}
