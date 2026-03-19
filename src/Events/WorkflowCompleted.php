<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use Illuminate\Queue\SerializesModels;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;

class WorkflowCompleted
{
    use SerializesModels;

    public $batch;

    public function __construct(WorkflowBatch $batch)
    {
        $this->batch = $batch;
    }
}
