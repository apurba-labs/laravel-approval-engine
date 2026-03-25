<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 

use ApurbaLabs\ApprovalEngine\Tests\Support\Factories\UserFactory;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $guarded = [];
    protected $table = 'users';

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Explicitly link the Test Factory
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
