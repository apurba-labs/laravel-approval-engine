<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;

class WorkflowStageAdvanced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WorkflowInstance $workflow,
        public string $fromRole,
        public string $toRole
    ) {}
}