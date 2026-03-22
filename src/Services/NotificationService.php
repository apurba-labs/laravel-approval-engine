<?php
namespace ApurbaLabs\ApprovalEngine\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendImmediateIfNeeded($notification)
    {
        $setting = $this->getSetting($notification);

        if ($setting?->frequency !== 'instant') {
            return;
        }

        $this->send($notification);
    }

    public function sendBatch($batch, $notifications)
    {
        $grouped = $notifications->groupBy('role');

        foreach ($grouped as $role => $items) {
            $this->sendBatchMail($role, $items, $batch);
        }
    }

    public function send($notification)
    {
        $channels = config('approval-engine.notification.channels');

        if ($channels['mail']) {
            $this->sendMail($notification);
        }

        if ($channels['slack']) {
            $this->sendSlack($notification);
        }
    }

    protected function sendMail($notification)
    {
        try {
            Mail::to($this->resolveRecipients($notification))
                ->send(new \App\Mail\WorkflowNotificationMail($notification));
        } catch (\Exception $e) {
            Log::error("Mail failed: " . $e->getMessage());
        }
    }

    protected function sendBatchMail($role, $items, $batch)
    {
        try {
            Mail::to($this->resolveBatchRecipients($role))
                ->send(new \App\Mail\WorkflowBatchMail($items, $batch));
        } catch (\Exception $e) {
            Log::error("Batch mail failed: " . $e->getMessage());
        }
    }

    protected function sendSlack($notification)
    {
        Log::info("Slack notification: " . json_encode($notification));
        // future: Slack API integration
    }

    protected function resolveRecipients($notification)
    {
        return \App\Models\User::role($notification->role)->pluck('email')->toArray();
    }

    protected function resolveBatchRecipients($role)
    {
        return \App\Models\User::role($role)->pluck('email')->toArray();
    }

    protected function getSetting($notification)
    {
        return \App\Models\WorkflowSetting::where('module', $notification->module)
            ->where('role', $notification->role)
            ->first();
    }
}