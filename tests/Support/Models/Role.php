<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory; 

use ApurbaLabs\ApprovalEngine\Tests\Support\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'roles';

    /**
     * Link to the Test Factory explicitly.
     */
    protected static function newFactory(): Factory
    {
        return RoleFactory::new();
    }
}
