<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

class CompleteWorkflowAction
{
    public function execute($batch)
    {
        $batch->update([
            'status'=>'completed'
        ]);
    }
}
