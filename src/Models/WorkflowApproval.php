<?php
namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowApproval extends Model
{
    protected $table = 'workflow_approvals';

    protected $fillable = [
        'batch_id',
        'user_id',
        'stage',
        'status',
        'approved_at',
        'comments'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];
}
