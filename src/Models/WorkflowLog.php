<?php
namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowLogFactory;

class WorkflowLog extends Model
{
    //use HasFactory;

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

    // Relationship removed to support dynamic Module Owners (creator/admin/user)

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowLogFactory::new();
    }
}
