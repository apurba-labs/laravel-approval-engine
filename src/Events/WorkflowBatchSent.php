<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowBatch;

class WorkflowBatchSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowBatch $batch
    ) {}
}