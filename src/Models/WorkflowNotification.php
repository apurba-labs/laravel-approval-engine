<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 

use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowNotificationFactory;

class WorkflowNotification extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_sent' => 'boolean',       // Allows if ($notification->is_sent)
        'sent_at' => 'datetime',
        'workflow_instance_id' => 'integer',
        'batch_id' => 'integer',
        'next_retry_at' => 'datetime',
        'escalate_at' => 'datetime',
    ];

    /**
     * The instance this notification belongs to.
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * The batch this notification was bundled into (for Daily/Weekly).
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(WorkflowBatch::class, 'batch_id');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowNotificationFactory::new();
    }
}
