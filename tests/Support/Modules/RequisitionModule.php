<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Requisition;

class RequisitionModule extends BaseWorkflowModule
{
    public function model(): string
    {
        return Requisition::class;
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

    public function customRelations(): array
    {
        return []; 
    }

    public function selectColumns(): array
    {
         return [
            'id',
            'user_id',
            'reference_id',
            'stage',
            'stage_status',
            'status',
            'approved_at',
        ];
    }

    public function displayColumns(): array
    {
        return [
            'reference_id' => 'Reference',
            'user.name' => 'Requested By',
        ];
    }

   public function relationModels(): array
    {
        return [
            'user' => \ApurbaLabs\ApprovalEngine\Tests\Support\Models\User::class,
            //'admin' => \App\Models\Admin::class,
        ];
    }
}
