<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

class WorkflowRejected
{
    public function __construct(
        public WorkflowInstance $workflow,
        public ?string $reason = null
    ) {}
}