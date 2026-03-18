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

    public function relations(): array
    {
        return [
            'user'
        ];
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

    public function query(): Builder
    {
        return $this->model()::query();
    }

}