<?php
namespace ApurbaLabs\ApprovalEngine\Support;

use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

class StageResolver
{
    public function getStage(string $module, string $role): int
    {
        $moduleName = is_string($module) ? $module : $module->name();

        $stage = WorkflowStage::where('module', $moduleName)
            ->where('role', $role)
            ->value('stage_order');

        if (!$stage) {
            throw new \RuntimeException("Stage not configured for role {$role} in module {$module}");
        }

        return $stage;
    }

    public function getCurrentStage($module, $stageOrder)
    {
        return WorkflowStage::where('module', $module)
            ->where('stage_order', $stageOrder)
            ->first();
    }

    public function getNextStage($module, $currentStage)
    {
        return WorkflowStage::where('module', $module)
            ->where('stage_order', '>', $currentStage)
            ->orderBy('stage_order')
            ->first();
    }

    public function getFirstStage($module)
    {
        return WorkflowStage::where('module', $module)
            ->orderBy('stage_order')
            ->first();
    }
}
