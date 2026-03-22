<?php
namespace ApurbaLabs\ApprovalEngine\Services;

use App\Models\WorkflowRule;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;

class WorkflowRuleResolver
{
    protected StageNavigator $stageNavigator;

    public function __construct(StageNavigator $stageNavigator)
    {
        $this->stageNavigator = $stageNavigator;
    }

    public function resolveNextStage($workflow)
    {
        $module = $workflow->module;
        $payload = $workflow->payload ?? [];

        $rules = WorkflowRule::where('module', $module)
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        foreach ($rules as $rule) {

            $value = $payload[$rule->field] ?? null;

            if ($value === null) continue;

            if ($this->evaluate($value, $rule->operator, $rule->value)) {

                return $this->stageNavigator
                    ->getStageByRole($module, $rule->role);
            }
        }

        // fallback
        return $this->stageNavigator->getNextStage(
            $module,
            $workflow->current_stage_order
        );
    }

    private function evaluate($left, $operator, $right): bool
    {
        return match ($operator) {
            '>'  => $left > $right,
            '<'  => $left < $right,
            '='  => $left == $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            default => false,
        };
    }
}