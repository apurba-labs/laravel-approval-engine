<?php

namespace ApurbaLabs\ApprovalEngine\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Relations\HasMany;
use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowModuleFactory;

class WorkflowModule extends Model
{
    use HasFactory;
    protected $table = 'workflow_modules';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'label',
        'icon',
        'is_active',
        'config',
        'source',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array', // Automatically handles JSON serialization
    ];

    /**
     * Relationship: A module has many workflow stages.
     */
    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class, 'module', 'slug');
    }

    /**
     * Relationship: A module has many workflow instances.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'module', 'slug');
    }

    /**
     * Scope: Only active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): Factory
    {
        return WorkflowModuleFactory::new();
    }
}
