<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowNotification::class;

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
            'is_sent' => false,
            // Automatically creates the parent Instance if not provided
            'workflow_instance_id' => WorkflowInstance::factory(), 
            'batch_id' => null,
        ];
    }
    public function forModule(string $name){ return $this->state(fn () => ['module' => $name]); }
    public function forRole(string $role) { return $this->state(fn() => ['role' => $role]); }
    public function sent() { return $this->state(fn() => ['is_sent' => true, 'sent_at' => now()]); }
}