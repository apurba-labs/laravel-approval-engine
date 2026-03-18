<?php

namespace App\Workflow\Modules;

use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;
use Illuminate\Database\Eloquent\Builder;

class TestModuleModule extends BaseWorkflowModule
{

    public function model()
    {
        // return \App\Models\SalesOrder::class;
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
            // 'customer', 'items'
        ];
    }

    public function displayColumns(): array
    {
        return [
            // 'order_no' => 'Order No',
            // 'amount' => 'Amount'
        ];
    }

    public function query(): Builder
    {
        return $this->model()::query();
    }

}