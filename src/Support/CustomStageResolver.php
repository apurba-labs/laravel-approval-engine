<?php

namespace App\Services;

use ApurbaLabs\ApprovalEngine\Support\StageResolver;
use App\Models\WorkflowStage;

class CustomStageResolver extends StageResolver
{
    /**
     * Resolve stages dynamically
     *
     * @param  mixed $module  Module object or class name
     * @param  array $stages  Existing stages
     * @return array
     */
    public function resolve($module, array $stages): array
    {
        $moduleName = is_string($module) ? $module : get_class($module);

        dump("Module Name: " . $moduleName);
        
        $rules = config('approval-engine.dynamic_stage_rules', []);

        if (isset($rules[$moduleName])) {
            $rule = $rules[$moduleName];

            $column = $rule['exception_column'] ?? null;
            $threshold = $rule['trigger_threshold'] ?? null;
            $role = $rule['role'] ?? null;

            if ($column && $threshold !== null && $role && isset($module->$column) && $module->$column > $threshold) {

                // Prevent duplicate
                $exists = collect($stages)->contains(fn($s) => ($s['role'] ?? null) === $role);

                if (!$exists) {
                    $stage = WorkflowStage::where('module', $moduleName)
                        ->where('role', $role)
                        ->first();

                    if ($stage) {
                        $stages[] = [
                            'role' => $role,
                            'stage_order' => $stage->stage_order
                        ];
                    }
                }
            }
        }

        return $stages;
    }

}
