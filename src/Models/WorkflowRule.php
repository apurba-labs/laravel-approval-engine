<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    protected $fillable = ['module', 'field', 'operator', 'value', 'role', 'priority'];
}