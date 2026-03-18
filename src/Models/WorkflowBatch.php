<?php
namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowBatch extends Model
{
    protected $table = 'workflow_batches';

    protected $fillable = [
        'role', 
        'module',
        'stage',
        'token',
        'window_start',
        'window_end',
        'item_count',
        'status',
        'sent_at',
        'acknowledged_at',
        'reminder_count',
        'last_reminder_at'
    ];

    protected $casts = [
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'sent_at' => 'datetime',
        'last_reminder_at' => 'datetime'
    ];

        /**
     * Generate unique token for batch
     */
    public static function generateToken(): string
    {
        return Str::uuid()->toString();
    }
}
