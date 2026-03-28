<?php
namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowApproval extends Model
{
    protected $table = 'workflow_approvals';

    protected $fillable = [
        'workflow_instance_id',
        'batch_id',
        'user_id',
        'stage_id',
        'stage_order',
        'status',
        'approved_at',
        'comments'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];
}
