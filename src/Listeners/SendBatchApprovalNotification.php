<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use Illuminate\Support\Facades\Mail;

class SendBatchApprovalNotification
{
    public function handle(BatchApproved $event)
    {
        $batch = $event->batch;

        // ⚡ For now simple static email (v1)
        $email = config('approval-engine.test_email', 'test@example.com');

        $link = url("/approvals/{$batch->token}");

        Mail::raw(
            "A batch has been approved.\n\nApprove next stage:\n{$link}",
            function ($message) use ($email) {
                $message->to($email)
                        ->subject('Batch Approved');
            }
        );
    }
}
