<?php

namespace ApurbaLabs\ApprovalEngine\Listeners;

use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Mail\WorkflowFinalizedMail;
use App\Models\User;

class HandleWorkflowCompletion
{
    public function handle(WorkflowCompleted $event)
    {
        $batch = $event->batch;
        $engine = app(\ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine::class);
        $module = $engine->getModule($batch->module);

        // 1. Get the actual Eloquent records
        $records = $engine->getApprovedRecords(
            $module->name(), 
            $batch->window_start, 
            $batch->window_end
        );

        // 2. Get the owner column dynamically from the module
        $ownerColumn = method_exists($module, 'getOwnerColumn') 
            ? $module->getOwnerColumn() 
            : 'user_id'; // Fallback default

        // 3. Group records by the owner found in that specific column
        $groupedByOwner = $records->groupBy($ownerColumn);

        foreach ($groupedByOwner as $ownerId => $userRecords) {
            // Find the user model dynamically
            $user = User::find($ownerId);
            
            if (!$user) continue;

            // 4. Send the personalized email for THEIR records only
            Mail::to($user->email)->send(new WorkflowFinalizedMail(
                $batch, 
                $module, 
                $userRecords // These are only the records belonging to this user
            ));
        }

        // 5. Finalize the source table status
        $engine->finalizeSourceRecords($module->name(), $records->pluck('id'));
    }
}
