<?php
namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;

class StartWorkflowAction
{
    public function __construct(
        protected WorkflowEngine $engine
    ) {}

    public function execute(string $module, array $payload)
    {
        $workflow = $this->engine->start($module, $payload);

        event(new WorkflowStarted($workflow));

        return $workflow;
    }
}