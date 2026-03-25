<?php
use Illuminate\Support\Facades\Cache;
use App\Models\WorkflowRule;
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
        $payload = $workflow->payload ?? [];

        $rules = Cache::rememberForever(
            "workflow_rules:{$module}",
            fn() => WorkflowRule::where('module', $module)
                ->where('is_active', true)
                ->orderBy('priority')
                ->get()
        );

        foreach ($rules as $rule) {

            $value = $payload[$rule->field] ?? null;

            if ($value === null) continue;

            if ($this->evaluate($value, $rule->operator, $rule->value)) {

                return $this->stageNavigator
                    ->getStageByRole($module, $rule->role);
            }
        }

        return $this->stageNavigator->getNextStage(
            $module,
            $workflow->current_stage_order
        );
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
}