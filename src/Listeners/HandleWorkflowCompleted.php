<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use Illuminate\Support\Facades\Mail;
//use ApurbaLabs\ApprovalEngine\Mail\WorkflowFinalizedMail;
use App\Models\User;

class HandleWorkflowCompleted
{
    public function handle(WorkflowCompleted $event)
    {
        $batch = $event->batch;
        $engine = app(\ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine::class);
        $module = $engine->getModule($batch->module);

        $records = $engine->getApprovedRecords(
            $module->name(), 
            $batch->window_start, 
            $batch->window_end
        );

        $notificationQueue = [];

        $groupedByOwner = $records->groupBy($ownerColumn);

        foreach ($records as $record) {
            $owner = $module->resolveOwner($record);

            if ($owner && !empty($owner->email)) {
                $notificationQueue[$owner->email]['owner'] = $owner;
                $notificationQueue[$owner->email]['records'][] = $record;
            }
        }

        // foreach ($notificationQueue as $email => $data) {
        //     Mail::to($email)->send(new WorkflowFinalizedMail(
        //         $batch, 
        //         $module, 
        //         collect($data['records']), 
        //         $data['owner']
        //     ));
        // }

        Log::info("Workflow COMPLETELY Finished: Batch #{$batch->id} for {$module->name()}");
    }
}
