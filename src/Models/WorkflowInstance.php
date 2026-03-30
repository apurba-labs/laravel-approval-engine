<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowInstanceFactory;
class WorkflowInstance extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'payload' => 'json',          // Converts array to JSON string automatically
        'current_stage_order' => 'integer',
        'started_at' => 'datetime',   // Allows $instance->started_at->format('Y-m-d')
        'completed_at' => 'datetime',
    ];

    /**
     * Get all notification attempts for this specific instance.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(WorkflowNotification::class, 'workflow_instance_id');
    }

    public function approvals()
    {
        return $this->hasMany(\ApurbaLabs\ApprovalEngine\Models\WorkflowApproval::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkflowLog::class)
            ->orderBy('entered_at');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowInstanceFactory::new();
    }
}
