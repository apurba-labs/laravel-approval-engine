<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;

use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowRuleFactory;

class WorkflowRule extends Model
{
    protected $fillable = ['module', 'field', 'operator', 'value', 'role', 'priority'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowRuleFactory::new();
    }
}