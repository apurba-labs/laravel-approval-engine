<?php
namespace ApurbaLabs\ApprovalEngine\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Approval extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ApurbaLabs\ApprovalEngine\Services\WorkflowManager::class;
    }
}