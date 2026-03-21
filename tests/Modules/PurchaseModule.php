<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;
use ApurbaLabs\ApprovalEngine\Tests\Models\Purchase;

class PurchaseModule extends BaseWorkflowModule
{
    /**
     * The Eloquent model this module manages.
     */
    public function model(): string
    {
        return Purchase::class;
    }
    
    public function approvedColumn(): string
    {
        return 'approved_at';
    }
    
    public function statusColumn(): string
    {
        return 'status';
    }

    /**
     * V1.2 FEATURE: Define the priority of owners.
     * We want to check 'admin' first, then 'creator'.
     */
    public function ownerRelations(): array
    {
        return ['user'];
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

    protected function customRelations(): array
    {
        return []; 
    }

    public function selectColumns(): array
    {
         return [];
    }

    public function displayColumns(): array
    {
        return [];
    }

}
