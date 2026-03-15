<?php

namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class WorkflowEngine
{
    /**
     * get Module Config
     */
    public function getModuleConfig($module)
    {
        return config("approval-engine.modules.$module");
    }

    /**
     * Resolve the module class from config and ensure it implements the interface.
     */
    public function getModule(string $moduleName): WorkflowModuleInterface
    {
        $class = $this->getModuleConfig($module);

        if (!$class || !class_exists($class)) {
            throw new RuntimeException("Workflow module [{$moduleName}] not found in config.");
        }

        $module = app($class);

        if (!$module instanceof WorkflowModuleInterface) {
            throw new RuntimeException("Module [{$class}] must implement WorkflowModuleInterface.");
        }

        return $module;
    }

    /**
     * Get records that have completed the approval process.
     */
    public function getApprovedRecords(string $moduleName, $start, $end): Collection
    {
        $module = $this->getModule($moduleName);
        $modelClass = $module->model();

        return $modelClass::query()
            ->select($module->selectColumns())
            ->with($module->relations())
            ->where($module->approvedColumn(), true) // Assuming boolean for "approved"
            ->whereBetween('created_at', [$start, $end]) // Or use a specific date column
            ->get();
    }
}
