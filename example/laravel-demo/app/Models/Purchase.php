<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';
    protected $fillable = ['user_id', 'created_by', 'total_amount', 'status', 'approved_at'];

    /**
     * Define the relationship as 'creator' to match your 
     * ownerRelations logic ['creator', 'user']
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
