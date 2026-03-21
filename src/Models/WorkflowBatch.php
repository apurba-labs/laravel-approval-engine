<?php
namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 
use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowBatchFactory;

class WorkflowBatch extends Model
{
    use HasFactory;

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

    protected static function newFactory(): Factory
    {
        return \ApurbaLabs\ApprovalEngine\Tests\Factories\WorkflowBatchFactory::new();
    }
}
