<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
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
}
