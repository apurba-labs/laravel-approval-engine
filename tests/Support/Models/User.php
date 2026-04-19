<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 
use ApurbaLabs\IAM\Contracts\Authorizable;

use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Traits\HasRoles;

use ApurbaLabs\ApprovalEngine\Tests\Support\Factories\UserFactory;

class User extends Authenticatable implements Authorizable
{
    use Notifiable, HasFactory, HasRoles;

    protected $guarded = [];
    protected $table = 'users';

    public function role(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'iam_role_user');
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
