<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowNotification extends Model
{
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_sent' => 'boolean',       // Allows if ($notification->is_sent)
        'sent_at' => 'datetime',
        'workflow_instance_id' => 'integer',
        'batch_id' => 'integer',
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
}
