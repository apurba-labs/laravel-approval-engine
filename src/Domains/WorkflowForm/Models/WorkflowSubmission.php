<?php

namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 

use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowSubmissionFactory;

class WorkflowSubmission extends Model
{
    use HasFactory;
    
    protected $table = 'workflow_submissions';

    protected $fillable = [
        'form_id',
        'workflow_instance_id',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function newFactory(): Factory
    {
        return WorkflowSubmissionFactory::new();
    }
}
