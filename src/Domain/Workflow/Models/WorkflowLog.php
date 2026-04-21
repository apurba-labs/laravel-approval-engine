<?php
namespace ApurbaLabs\ApprovalEngine\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ApurbaLabs\ApprovalEngine\Support\Traits\HasTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\Factory; 
use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowLogFactory;

use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;

class WorkflowLog extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'workflow_logs';

    protected $guarded = [];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    // Relationship removed to support dynamic Module Owners (creator/admin/user)

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowLogFactory::new();
    }
}
