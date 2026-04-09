<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Support\BatchProcessor;
use ApurbaLabs\ApprovalEngine\Support\BatchWindowResolver;
use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;

class SendWorkflowBatchCommand extends Command
{
    protected $signature = 'approval:send-batch {--force}';

    protected $description = 'Process workflow approval batches';

    public function handle()
    {
        $processor = app(BatchProcessor::class);
        $windowResolver = app(BatchWindowResolver::class);
        $notificationService = app(NotificationService::class);

        $pendingNotifications = WorkflowNotification::query()
            ->where('status', 'pending')
            ->whereNull('batch_id')
            ->get()
            ->groupBy(fn ($notification) =>
                $notification->module . '|' . $notification->recipient_signature
            );

        if ($pendingNotifications->isEmpty()) {
            $this->info('No pending notifications found.');
            return Command::SUCCESS;
        }

        foreach ($pendingNotifications as $groupKey => $notifications) {

            $first = $notifications->first();

            $setting = $this->resolveSetting($first);

            if (!$setting) {
                $this->warn("No workflow setting found for group: {$groupKey}");
                continue;
            }

            if (!$this->shouldSendToday($setting)) {
                continue;
            }

            $window = $windowResolver->resolve($setting);

            $start = $window['start'];
            $end = $window['end'];

            $existing = $processor->findExistingBatch(
                $first->module,
                $first->recipient_signature,
                $start,
                $end
            );

            if ($existing) {
                continue;
            }

            $batch = $processor->createBatch(
                module: $first->module,
                recipientSignature: $first->recipient_signature,
                start: $start,
                end: $end,
                role: $first->role,
                assignType: $first->assign_type,
                assignValue: $first->assign_value
            );

            WorkflowNotification::whereIn('id', $notifications->pluck('id'))
                ->update([
                    'batch_id' => $batch->id,
                ]);

            $notificationService->sendBatch($batch, $notifications);

            $processor->markSent($batch, $notifications->count());

            $setting->update([
                'last_run_at' => now(),
            ]);

            $this->info("Batch sent for {$groupKey}");
        }

        return Command::SUCCESS;
    }

    protected function resolveSetting($notification): ?WorkflowSetting
    {
        return WorkflowSetting::query()
            ->where('module', $notification->module)
            ->where(function ($q) use ($notification) {
                $q->where(function ($sub) use ($notification) {
                    $sub->where('assign_type', $notification->assign_type)
                        ->where('assign_value', $notification->assign_value);
                })->orWhere('role', $notification->role);
            })
            ->first();
    }

    protected function shouldSendToday($setting): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $timezone = $setting->timezone ?? config('app.timezone');

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
            return false;
        }

        return match ($setting->frequency) {
            'daily' => true,
            'weekly' => (int) $now->dayOfWeek === (int) $setting->weekly_day,
            'monthly' => (int) $now->day === (int) $setting->monthly_date,
            default => false,
        };
    }
}