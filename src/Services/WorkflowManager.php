<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Actions\StartWorkflowAction;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;

class WorkflowManager
{
    public function __construct(
        protected WorkflowEngine $engine
    ) {}

    public function start(string $module, array $payload)
    {
        return app(StartWorkflowAction::class)
            ->execute($module, $payload);
    }

    public function approve(int|string $workflowId, int|string|null $userId = null)
    {
        $workflow = WorkflowInstance::findOrFail($workflowId);

        $userId = $userId ?? 1; // fallback for test

        return $this->engine->approve($workflow, $userId); 
    }

    public function reject(int|string $workflowId, int|string $userId, ?string $reason = null)
    {
        $workflow = WorkflowInstance::findOrFail($workflowId);

        return $this->engine->reject($workflow, $userId, $reason);
    }
}