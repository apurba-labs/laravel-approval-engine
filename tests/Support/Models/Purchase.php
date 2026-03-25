<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 

use ApurbaLabs\ApprovalEngine\Tests\Support\Factories\PurchaseFactory;

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
        return $this->belongsTo(User::class, 'user_id');
    }
    protected static function newFactory(): Factory
    {
        return PurchaseFactory::new();
    }
}
