<?php

namespace ApurbaLabs\ApprovalEngine\Modules;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;

abstract class BaseWorkflowModule implements WorkflowModuleInterface
{
    /**
     * Default status column name. 
     * Override this in the child class if it differs.
     */
    public function statusColumn(): string
    {
        return 'status';
    }

    /**
     * Default approval boolean column name.
     */
    public function approvedColumn(): string
    {
        return 'is_approved';
    }

    /**
     * By default, select all columns.
     */
    public function selectColumns(): array
    {
        return ['*'];
    }

    public function relations(): array
    {
        return [];
    }

    public function displayColumns(): array
    {
        return [];
    }
}
