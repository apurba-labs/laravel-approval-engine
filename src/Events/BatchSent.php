<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;

class BatchSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowBatch $batch
    ) {}
}