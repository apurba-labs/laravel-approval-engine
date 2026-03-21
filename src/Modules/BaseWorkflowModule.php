<?php

namespace ApurbaLabs\ApprovalEngine\Modules;

use Illuminate\Database\Eloquent\Builder;
use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;

abstract class BaseWorkflowModule implements WorkflowModuleInterface
{
    /**
     * Method to Modules. 
     * Example: SalesOrderModule → salesorder, PurchaseOrderModule → purchaseorder
     */
    public function name(): string
    {
        return strtolower(
            str_replace('Module','',class_basename($this))
        );
    }

    /**
     * Retrieve the workflow settings for this specific module.
     * If a stage is provided, it returns settings for that stage.
     */
    public function getSettings($role = null)
    {
        $query = \ApurbaLabs\ApprovalEngine\Models\WorkflowSetting::where('module', $this->name())
            ->where('is_active', true);

        if ($role) {
            return $query->where('role', $role)->first();
        }

        return $query->get();
    }
    
    /**
     * Validate records before they enter a batch.
     * Useful for checking data integrity or custom business rules.
     */
    public function validate(array $data): void
    {
        // Default: No validation required
    }

    /**
     * Default status column name. 
     * Override this in the child class if it differs.
     */
    public function statusColumn(): string
    {
        return 'status';
    }

    /**
     * Default approval timestamp column name.
     */
    public function approvedColumn(): string
    {
        return 'approved_at';
    }

    /**
     * By default, select all columns.
     */
    public function selectColumns(): array
    {
        return ['*'];
    }

    
    /**
     * Default priorities: check for 'user', then 'creator'.
     * Individual modules can override this.
     */
    public function ownerRelations(): array
    {
        return ['user', 'creator'];
    }

    /**
     * Automatically merge priorities into eager loading.
     */
    public function relations(): array
    {
        // Start with any custom relations the developer needs
        $customRelations = $this->customRelations();

        // Merge in the owner priorities so they are always eager-loaded
        return array_unique(array_merge(
            $customRelations, 
            $this->ownerRelations()
        ));
    }

    /**
     * Allow developers to add extra relations (like 'items' or 'department').
     */
    protected function customRelations(): array
    {
        return [];
    }

    public function displayColumns(): array
    {
        return [];
    }

    public function query(): Builder
    {
        $model = $this->model();
        return $model::query();
    }
}
