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
     * Default priorities: check for 'creator', then 'user'.
     * Individual modules can override this.
     */
    public function ownerRelations(): array
    {
        return ['creator', 'user'];
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
