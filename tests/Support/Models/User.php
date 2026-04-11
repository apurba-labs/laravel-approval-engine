<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 
use ApurbaLabs\IAM\Contracts\Authorizable;
use ApurbaLabs\IAM\Traits\HasRoles;

use ApurbaLabs\ApprovalEngine\Tests\Support\Factories\UserFactory;

class User extends Authenticatable implements Authorizable
{
    use Notifiable, HasFactory, HasRoles;

    protected $guarded = [];
    protected $table = 'users';

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Implementation of the Contract
     * We wrap your service logic here
     */
    public function canIam(string $permission, $scopeId = null): bool
    {
        return app('iam')->can($this, $permission, $scopeId);
    }

    /**
     * Explicitly link the Test Factory
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
