<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;

class SendBatchApprovalNotification
{
    public function handle(BatchApproved $event)
    {
        $batch = $event->batch;
        
        $engine = app(\ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine::class);

        $module = $engine->getModule($batch->module);
        if (!$module) {
            \Log::error("Workflow module not found for batch: " . $batch->module);
            return;
        }

        $records = $engine->getApprovedRecords(
            $module->name(), 
            $batch->window_start, 
            $batch->window_end
        );
        
        $email = config('approval-engine.test_email', 'apurbansingh@yahoo.com');
        Mail::to($email)->send(new BatchApprovalMail(
            $batch, 
            $records, 
            $module
        ));
        
    }
}
