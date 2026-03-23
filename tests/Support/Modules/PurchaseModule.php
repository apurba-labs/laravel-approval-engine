<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Purchase;

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
     * We want to check 'creator' first, then 'admin'.
     */
    public function ownerRelations(): array
    {
        return ['creator'];
    }

    public function customRelations(): array
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
