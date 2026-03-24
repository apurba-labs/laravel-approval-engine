<?php
namespace ApurbaLabs\ApprovalEngine\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;

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
        // V1.3 Placeholder: Log the intent instead of sending a real email
        // This allows you to test the BatchProcessor logic without a Mail Server
        \Log::info("Workflow Batch Notification Ready", [
            'notice'   => 'Real Mail/Notification delivery will be implemented in V1.4'
        ]);

    }

    protected function sendBatchMail($role, $items, $batch)
    {

        // V1.3 Placeholder: Log the intent instead of sending a real email
        // This allows you to test the BatchProcessor logic without a Mail Server
        \Log::info("Workflow Batch Notification Ready", [
            'module'   => $batch->module,
            'role'     => $role,
            'items'    => count($items),
            'batch_id' => $batch->id,
            'notice'   => 'Real Mail/Notification delivery will be implemented in V1.4'
        ]);

        //Return true so the BatchProcessor marks these as 'sent' for now
        return true; 
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
        return WorkflowSetting::where('module', $notification->module)
            ->where('role', $notification->role)
            ->first();
    }
}