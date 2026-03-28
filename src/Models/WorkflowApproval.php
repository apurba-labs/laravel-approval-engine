<?php
namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

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
        'comments',
        'due_at',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'assigned_at'  => 'datetime',
        'due_at'       => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }
}
