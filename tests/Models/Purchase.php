<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;
    protected $table = 'purchases';
    protected $fillable = ['created_by', 'total_amount', 'status'];

    /**
     * Define the relationship as 'creator' to match your 
     * ownerRelations logic ['creator', 'user']
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    protected static function newFactory()
    {
        return \ApurbaLabs\ApprovalEngine\Tests\Factories\PurchaseFactory::new();
    }
}
