<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module' => 'requisition',
            'field' => 'total_amount',
            'operator' => '>',
            'value' => '10000',
            'role' => 'COO',
            'priority' => 1,
            'is_active' => true,
        ];
    }

    public function forModule(string $name)
    {
        return $this->state(fn () => ['module' => $name]);
    }

    public function forField(string $field)
    {
        return $this->state(fn () => ['field' => $field]);
    }

    public function withOperator(string $operator)
    {
        return $this->state(fn () => ['operator' => $operator]);
    }

    public function withValue($value)
    {
        return $this->state(fn () => ['value' => (string) $value]);
    }

    public function targetRole(string $role)
    {
        return $this->state(fn () => ['role' => $role]);
    }
    public function withPriority(int $priority)
    {
        return $this->state(fn () => ['priority' => $priority]);
    }
}
