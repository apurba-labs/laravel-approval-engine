<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $table = 'purchases';
    protected $fillable = ['created_by', 'total_amount', 'status'];

    /**
     * Define the relationship as 'creator' to match your 
     * ownerRelationPriorities logic ['creator', 'user']
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
