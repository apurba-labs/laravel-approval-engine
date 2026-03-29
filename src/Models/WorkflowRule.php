<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 
use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowRuleFactory;

class WorkflowRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'module', 
        'field', 
        'operator', 
        'value', 
        'role', 
        'assign_type',
        'assign_value',
        'priority', 
        'is_active'
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowRuleFactory::new();
    }
}