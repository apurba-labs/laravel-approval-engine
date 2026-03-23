<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowInstanceFactory::new();
    }
}
