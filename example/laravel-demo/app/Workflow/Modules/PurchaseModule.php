<?php
namespace App\Workflow\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;

class PurchaseModule extends BaseWorkflowModule
{
    /**
     * The Eloquent model this module manages.
     */
    public function model(): string
    {
        return \App\Models\Purchase::class;
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
        return ['user'];
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

    public function relationModels(): array
    {
        return [
            'user' => \App\Models\User::class,
            //'admin' => \App\Models\Admin::class,
        ];
    }

}
