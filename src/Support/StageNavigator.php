<?php

namespace ApurbaLabs\ApprovalEngine\Support;

use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

/**
 * Class StageNavigator
 * 
 * Responsible for traversing the workflow hierarchy by querying 
 * the database-driven stage configurations.
 */
class StageNavigator
{
    /**
     * Retrieve the entry-point stage for a specific module.
     *
     * @param string $module
     * @return \ApurbaLabs\ApprovalEngine\Models\WorkflowStage|null
     */
    public function getFirstStage(string $module)
    {
        return WorkflowStage::where('module', $module)
            ->orderBy('stage_order')
            ->first();
    }

    /**
     * Retrieve a specific stage by its defined order.
     *
     * @param string $module
     * @param int $stageOrder
     * @return \ApurbaLabs\ApprovalEngine\Models\WorkflowStage|null
     */
    public function getCurrentStage(string $module, int $stageOrder)
    {
        return WorkflowStage::where('module', $module)
            ->where('stage_order', $stageOrder)
            ->first();
    }

    /**
     * Identify the subsequent stage in the workflow sequence.
     * Used for moving an instance forward after an approval.
     *
     * @param string $module
     * @param int $currentStageOrder
     * @return \ApurbaLabs\ApprovalEngine\Models\WorkflowStage|null
     */
    public function getNextStage(string $module, int $currentStageOrder)
    {
        return WorkflowStage::where('module', $module)
            ->where('stage_order', '>', $currentStageOrder)
            ->orderBy('stage_order')
            )
            ->first();
    }

    /**
     * Find a stage based on a specific organizational role.
     * Useful for jumping to stages via custom Rule Resolvers.
     *
     * @param string $module
     * @param string $role
     * @return \ApurbaLabs\ApprovalEngine\Models\WorkflowStage|null
     */
    public function getStageByRole(string $module, string $role)
    {
        return WorkflowStage::where('module', $module)
            ->where('role', $role)
            ->first();
    }
}
