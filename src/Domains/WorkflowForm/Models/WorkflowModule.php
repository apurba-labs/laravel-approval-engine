<?php

namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 

use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowModuleFactory;

class WorkflowModule extends Model
{
    use HasFactory;
    
    protected $table = 'workflow_modules';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function newFactory(): Factory
    {
        return WorkflowModuleFactory::new();
    }

}
