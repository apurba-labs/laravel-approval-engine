<?php
namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use ApurbaLabs\ApprovalEngine\Support\BatchProcessor;
use ApurbaLabs\ApprovalEngine\Support\BatchWindowResolver;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;

class SendWorkflowBatchCommand extends Command
{
    protected $signature = 'approval:send-batch {--force : Ignore last_run_at and send anyway}';

    protected $description = 'Process workflow approval batches';

    public function handle()
    {
        $engine = app(WorkflowEngine::class);
        $processor = app(BatchProcessor::class);
        $windowResolver = app(BatchWindowResolver::class);
        $notificationService = app(NotificationService::class);

        $modules = $engine->discoverModules();

        if (empty($modules)) {
            $this->warn('No workflow modules discovered.');
            return Command::SUCCESS;
        }

        foreach ($modules as $module) {

            $moduleName = $module->name();
            $this->info("Processing module: {$moduleName}");

            $settingsList = WorkflowSetting::where('module', $moduleName)
                ->where('is_active', true)
                ->get();

            foreach ($settingsList as $settings) {

                if (!$this->shouldSendToday($settings)) {
                    continue;
                }

                $settings->update(['last_run_at' => now()]);

                $this->info("Processing role: {$settings->role}");

                $window = $windowResolver->resolve($settings);
                $start = $window['start'] ?? now()->subDay();
                $end = $window['end'] ?? now();

                $existing = $processor->findExistingBatch($moduleName, $settings->role, $start, $end);
                if ($existing) {
                    $this->line("Batch already exists, skipping...");
                    continue;
                }
                // Notifications
                $notifications = WorkflowNotification::where('module', $moduleName)
                    ->where('role', $settings->role)
                    ->where('status', 'pending')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();

                if ($notifications->isEmpty()) {
                    $this->line(" - No pending notifications.");
                    continue;
                }

                try {
                    // create batch (no stage needed anymore)
                    $batch = $processor->createBatch(
                        $moduleName,
                        $settings->role,
                        $start,
                        $end
                    );

                    //Send batch email
                    $notificationService->sendBatch($batch, $notifications);

                    // Mark notifications sent
                    WorkflowNotification::whereIn('id', $notifications->pluck('id'))
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                    // Update batch
                    $processor->markSent($batch, $notifications->count());

                    $this->info("Batch sent ({$notifications->count()} items)");

                } catch (\Exception $e) {
                    $this->error("Batch failed: " . $e->getMessage());
                }
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
