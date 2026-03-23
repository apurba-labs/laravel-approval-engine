<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Factories;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class; // Points to the Support Model

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role' => 'HOSD',
        ];
    }
}
