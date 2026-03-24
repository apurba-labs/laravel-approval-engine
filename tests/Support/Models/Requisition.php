<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 

use ApurbaLabs\ApprovalEngine\Tests\Support\Factories\RequisitionFactory;

class Requisition extends Model
{
    protected $table = 'requisitions';

    protected $fillable = [
        'user_id',
        'reference_id',
        'total_amount',
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
    protected static function newFactory(): Factory
    {
        return RequisitionFactory::new();
    }
}
