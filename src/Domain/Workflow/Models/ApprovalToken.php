<?php

namespace ApurbaLabs\ApprovalEngine\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ApurbaLabs\ApprovalEngine\Support\Traits\HasTenant;

class ApprovalToken extends Model
{
    use HasTenant;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'workflow_instance_id',
        'user_id',
        'token',
        'expires_at',
        'used_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Relationship: The workflow instance this token belongs to.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * Relationship: The user this token was issued to.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model') ?? \App\Models\User::class;
        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Scope: Only valid (not expired and not used) tokens.
     */
    public function scopeIsValid($query)
    {
        return $query->whereNull('used_at')
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /**
     * Helper: Check if token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
