<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use Illuminate\Support\Str;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowBatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowBatch::class;

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
            'token' => Str::random(32),
            'stage' => 1,
            'window_start' => now()->subDay(),
            'window_end' => now(),
            'status' => 'pending',
        ];
    }

    public function forModule(string $name){ return $this->state(fn () => ['module' => $name]); }
    public function forRole(string $role) { return $this->state(fn() => ['role' => $role]); }
    public function withToken(string $token){ return $this->state(fn () => ['token' => $token]); }
    public function atStage(int $order){ return $this->state(fn () => ['stage' => $order]); }
    public function completed() { return $this->state(fn() => ['status' => 'completed', 'completed_at' => now()]); }
}
