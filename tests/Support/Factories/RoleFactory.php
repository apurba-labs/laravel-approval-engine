<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Support\Factories;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => 'HOSD',
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Set a dynamic module for the stage.
     */
    public function forName(string $name)
    {
        return $this->state(fn () => ['name' => $name]);
    }
}
