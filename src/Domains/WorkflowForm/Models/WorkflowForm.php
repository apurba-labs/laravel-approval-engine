<?php

namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowModule;
use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowFormFactory;

class WorkflowForm extends Model
{
    use HasFactory;

    protected $table = 'workflow_forms';

    protected $fillable = [
        'module_id',
        'version',
        'schema',
        'is_active',
    ];

    protected $casts = [
        'schema' => 'array',
    ];

    public function workflowModule()
    {
        return $this->belongsTo(WorkflowModule::class, 'module_id');
    }

    protected static function newFactory(): Factory
    {
        return WorkflowFormFactory::new();
    }
}
