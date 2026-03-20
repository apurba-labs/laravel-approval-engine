<?php
namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;

class NotifyWorkflowStart
{
    /**
     * Handle the event.
     *
     * @param WorkflowStarted $event
     * @return void
     */
    public function handle(WorkflowStarted $event)
    {
        foreach ($event->workflows() as $workflow) {
            //  Notification or logging logic here
            info("Workflow started: ID {$workflow->id}, Module: {$workflow->module}");
        }
    }
}
