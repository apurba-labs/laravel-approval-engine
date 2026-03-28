<?php
namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRuleResolver;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;

class StartWorkflowAction
{
    public function __construct(
        protected WorkflowRuleResolver $ruleResolver,
        protected WorkflowRecipientResolver $recipientResolver,
        protected NotificationService $notificationService
    ) {}

    public function execute(string $module, array $payload)
    {
        // Create instance
        $workflow = WorkflowInstance::create([
            'module' => $module,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        // Resolve next stage
        $stage = $this->ruleResolver->resolveNextStage($workflow);

        // Find matching rule
        $rule = $this->findMatchingRule($module, $payload);

        // Resolve recipient
        $recipient = $this->recipientResolver->resolve($rule);

        // 5. Create approval
        $approval = WorkflowApproval::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => $recipient?->id,
            'stage_id' => $stage?->id,
            'status' => 'pending',
        ]);

        // Send notification
        $this->notificationService->sendSingle($approval);

        return $workflow;
    }

    protected function findMatchingRule($module, $payload)
    {
        return \ApurbaLabs\ApprovalEngine\Models\WorkflowRule::where('module', $module)
            ->where('is_active', true)
            ->orderBy('priority')
            ->get()
            ->first(function ($rule) use ($payload) {
                $value = $payload[$rule->field] ?? null;

                if ($value === null) return false;

                return match ($rule->operator) {
                    '>'  => $value > $rule->value,
                    '<'  => $value < $rule->value,
                    '='  => $value == $rule->value,
                    '>=' => $value >= $rule->value,
                    '<=' => $value <= $rule->value,
                    '!=' => $value != $rule->value,
                    default => false,
                };
            });
    }
}