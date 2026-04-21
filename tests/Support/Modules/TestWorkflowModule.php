<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Modules;

use Illuminate\Database\Eloquent\Builder;
use ApurbaLabs\ApprovalEngine\Modules\BaseWorkflowModule;

class TestWorkflowModule extends BaseWorkflowModule
{
    public function name(): string
    {
        return 'requisition';
    }

    public function model(): string
    {
        return \stdClass::class;
    }

    public function ownerRelations(): array
    {
        return [];
    }

    public function customRelations(): array
    {
        return [];
    }

    public function approvedColumn(): string
    {
        return 'approved_at';
    }

    public function statusColumn(): string
    {
        return 'status';
    }

    public function selectColumns(): array
    {
        return ['*'];
    }

    public function displayColumns(): array
    {
        return [];
    }

    public function query(): Builder
    {
        return new class extends Builder {
            public function __construct() {}
        };
    }
}