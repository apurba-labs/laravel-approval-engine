<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowStageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowStage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module' => 'requisition',
            'role' => 'HOSD',
            'stage_order' => 1,
        ];
    }
    
    /**
     * Set a dynamic module for the stage.
     */
    public function forModule(string $name)
    {
        return $this->state(fn () => ['module' => $name]);
    }

    /**
     * Set a dynamic role for the stage.
     */
    public function forRole(string $name)
    {
        return $this->state(fn () => ['role' => $name]);
    }

    /**
     * Set a dynamic stage order.
     */
    public function atStage(int $order)
    {
        return $this->state(fn () => ['stage_order' => $order]);
    }
}
