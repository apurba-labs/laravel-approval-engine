<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requisition extends Model
{
    protected $table = 'requisitions';

    protected $fillable = [
        'user_id',
        'reference_id',
        'type',
        'stage',
        'stage_status',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'stage' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
