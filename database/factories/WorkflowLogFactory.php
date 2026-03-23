<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_instance_id' => WorkflowInstance::factory(),
            'module' => 'requisition',
            'role' => 'HOSD',
            'stage_order' => 1,
            'entered_at' => now(),
            'exited_at' => null,
        ];
    }

    public function forModule(string $name){ return $this->state(fn () => ['module' => $name]); }
    public function forRole(string $role) { return $this->state(fn() => ['role' => $role]); }
    public function atStage(int $order){ return $this->state(fn () => ['stage_order' => $order]); }
    public function exited() { return $this->state(fn() => ['exited_at' => now()]); }
}
