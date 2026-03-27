<?php
namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowModule;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Str;

class WorkflowModuleFactory extends Factory
{

    protected $model = WorkflowModule::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }
    
    /**
     * Specifically create an "Expense" module.
     */
    public function expense(): self
    {
        return $this->state([
            'name' => 'Expense Approval',
            'slug' => 'expense',
        ]);
    }

    public function forName(string $name): self
    {
        return $this->state(['name' => $name]);
    }

    public function forSlug(string $slug): self
    {
        return $this->state(['slug' => $slug]);
    }
}
