<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowInstanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowInstance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module' => 'requisition',
            'current_stage_order' => 1,
            'role' => 'HOSD',
            'status' => 'pending',
            'payload' => ['total_amount' => 5000],
            'started_at' => now(),
        ];
    }

    public function forModule(string $name) { return $this->state(fn() => ['module' => $name]); }
    public function atStage(int $order) { return $this->state(fn() => ['current_stage_order' => $order]); }
    public function forRole(string $role) { return $this->state(fn() => ['role' => $role]); }
}
