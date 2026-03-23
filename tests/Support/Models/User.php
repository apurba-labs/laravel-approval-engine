<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ApurbaLabs\ApprovalEngine\Tests\Factories\UserFactory;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $guarded = [];
    protected $table = 'users';

    /**
     * Explicitly link the Test Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
