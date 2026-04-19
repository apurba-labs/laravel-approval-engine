<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Support\Factories;

use ApurbaLabs\IAM\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

use ApurbaLabs\IAM\Models\Role;

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
