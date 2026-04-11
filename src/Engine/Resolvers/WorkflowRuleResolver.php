<?php
namespace ApurbaLabs\ApprovalEngine\Engine\Resolvers;

use Illuminate\Support\Facades\Cache;
use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;

/**
 * Services + Cached + Safe
 */
class WorkflowRuleResolver
{
    public function __construct(protected StageNavigator $stageNavigator) {}

    public function resolveNextStage($workflow)
    {
        $module = $workflow->module;

        $rule = $this->findMatchingRule($workflow);

        if ($rule) {
            $stage = $this->stageNavigator
                ->getStageByRole($module, $rule->role);
                
            if ($stage && $rule->assign_type && $rule->assign_value) {
                $stage->resolved_assign_type = $rule->assign_type;
                $stage->resolved_assign_value = $rule->assign_value;
            }
            return $stage;
        }

        return $this->stageNavigator->getNextStage(
            $module,
            $workflow->current_stage_order
        );
    }

    protected function evaluateRule($payload, $rule): bool
    {
        $value = $payload[$rule->field] ?? null;

        if ($value === null) return false;

        return $this->evaluate($value, $rule->operator, $rule->value);
    }

    private function evaluate($left, $op, $right): bool
    {
        // Cast to numeric if both sides look like numbers to prevent string comparison bugs
        if (is_numeric($left) && is_numeric($right)) {
            $left = (float) $left;
            $right = (float) $right;
        }

        return match ($op) {
            '>'  => $left > $right,
            '<'  => $left < $right,
            '='  => $left == $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            '!=' => $left != $right,
            default => false,
        };
    }

    public function findMatchingRule($workflow): ?WorkflowRule
    {
        $module = $workflow->module;
        $payload = $workflow->payload ?? [];

        $rules = Cache::rememberForever(
            "workflow_rules:{$module}",
            fn() => WorkflowRule::where('module', $module)
                ->where('is_active', true)
                ->orderBy('priority')
                ->get()
        );

        foreach ($rules as $rule) {

            if (!array_key_exists($rule->field, $payload)) {
                continue;
            }

            if ($this->evaluateRule($payload, $rule)) {
                return $rule;
            }
        }

        return null;
    }
}