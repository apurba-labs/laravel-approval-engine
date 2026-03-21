<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

class HandleBatchApproved
{
    public function handle(BatchApproved $event)
    {
        $batch = $event->batch;
        $engine = app(WorkflowEngine::class);

        $module = $engine->getModule($batch->module);
        
        if (!$module) {
            \Log::error("Module not found for batch: " . $batch->module);
            return;
        }

        // Fetching the actual data records that were just approved
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
