<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use Illuminate\Support\Facades\Mail;
//use ApurbaLabs\ApprovalEngine\Mail\SingleWorkflowMail; 
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use Illuminate\Support\Facades\Log;

class HandleWorkflowStarted
{
    /**
     * Handle the event.
     * We only handle non-batch (single) starts here
     *
     * @param WorkflowStarted $event
     * @return void
     */
    public function handle(WorkflowStarted $event)
    {
        if ($event->isBatch()) {
            return;
        }

        foreach ($event->getWorkflows() as $workflow) {
            // Logic: Tell the first approver "Hey, there is a new request"
            Log::info("New Workflow Started: {$workflow['module']}");
        }
    }
}
