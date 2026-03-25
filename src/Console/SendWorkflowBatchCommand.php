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

        // START FROM SETTINGS
        $settingsList = WorkflowSetting::where('is_active', 1)->get();

        if ($settingsList->isEmpty()) {
            $this->warn('No active workflow settings found.');
            return Command::SUCCESS;
        }

        foreach ($settingsList as $settings) {

            $moduleName = $settings->module;

            $this->info("Processing module: {$moduleName} | role: {$settings->role}");

            // validate module exists
            try {
                $module = $engine->getModule($moduleName);
            } catch (\Exception $e) {
                $this->error("Module not found: {$moduleName}");
                continue;
            }

            // schedule check
            if (!$this->shouldSendToday($settings)) {
                $this->line("Skipping (schedule not matched)");
                continue;
            }

            $window = $windowResolver->resolve($settings);
            $start = $window['start'] ?? now()->subDay();
            $end = $window['end'] ?? now();

            // prevent duplicate batch
            $existing = $processor->findExistingBatch(
                $moduleName,
                $settings->role,
                $start,
                $end
            );

            if ($existing) {
                $this->line("Batch already exists, skipping...");
                continue;
            }

            // 9fetch notifications
            $notifications = WorkflowNotification::where('module', $moduleName)
                ->where('role', $settings->role)
                ->where('status', 'pending')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $this->line("Pending notifications: {$notifications->count()}");

            if ($notifications->isEmpty()) {
                continue;
            }

            try {
                // create batch
                $batch = $processor->createBatch(
                    $moduleName,
                    $settings->role,
                    $start,
                    $end
                );

                // send
                $notificationService->sendBatch($batch, $notifications);

                // mark notifications
                WorkflowNotification::whereIn('id', $notifications->pluck('id'))
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                // update batch
                $processor->markSent($batch, $notifications->count());

                // update setting AFTER success
                $settings->update(['last_run_at' => now()]);

                $this->info("Batch sent ({$notifications->count()})");

            } catch (\Exception $e) {
                $this->error("Batch failed: " . $e->getMessage());

                if (isset($batch)) {
                    $processor->markFailed($batch, $e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }

    private function shouldSendToday($setting)
    {
        if ($this->option('force')) return true;

        $timezone = $setting->timezone ?? config('app.timezone', 'UTC');
        $now = now()->timezone($timezone);

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
