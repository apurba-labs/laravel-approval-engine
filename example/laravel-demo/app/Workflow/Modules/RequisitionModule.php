<?php

namespace App\Workflow\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;
use Illuminate\Database\Eloquent\Builder;

class RequisitionModule extends BaseWorkflowModule
{

    public function model():string
    {
        return \App\Models\Requisition::class;
    }

    public function statusColumn(): string
    {
        return 'status';
    }

    public function approvedColumn(): string
    {
        return 'approved_at';
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

    /**
     * V1.3 FEATURE: Define the priority of owners class.
     */
    public function relationModels(): array
    {
        return [
            'user' => \App\Models\User::class,
            //'admin' => \App\Models\Admin::class,
        ];
    }

    public function query(): Builder
    {
        return $this->model()::query();
    }

}