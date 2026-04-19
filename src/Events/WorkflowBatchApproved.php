<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowBatchApproved
{
    use Dispatchable, SerializesModels;

    public $batch;

    public function __construct(WorkflowBatch $batch)
    {
        $this->batch = $batch;
    }
}
