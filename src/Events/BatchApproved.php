<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatchApproved
{
    use Dispatchable, SerializesModels;

    public $batch;

    public function __construct(WorkflowBatch $batch)
    {
        $this->batch = $batch;
    }
}
