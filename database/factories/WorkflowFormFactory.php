<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowModule;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowForm;

/**
 * @extends Factory<Model>
 */
class WorkflowFormFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowForm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Automatically creates a module if one isn't provided
            'module_id' => WorkflowModule::factory(), 
            'version' => 1,
            'schema' => [
                'fields' => [
                    ['name' => 'amount', 'type' => 'number', 'required' => true],
                ]
            ],
            'is_active' => true,
        ];
    }
    
    /**
     * State to quickly set a specific schema.
     */
    public function withSchema(array $fields)
    {
        return $this->state(fn () => [
            'schema' => ['fields' => $fields]
        ]);
    }
}
