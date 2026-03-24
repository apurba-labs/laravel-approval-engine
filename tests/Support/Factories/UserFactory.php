<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Support\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;

class UserFactory extends Factory
{
    protected $model = User::class; // Points to the Support Model

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role_id' => Role::factory(),
        ];
    }
    /**
     * Assign a role to the user, creating it only if it doesn't exist.
     */
    public function withRole(string $roleName, string $description = null)
    {
        return $this->state(function () use ($roleName, $description) {
            // Find or create the Role object
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $description ?? "System generated {$roleName} role"]
            );

            // Return ONLY the column that exists in your users table
            return [
                'role_id' => $role->id,
            ];
        });
    }

    /**
     * Set a dynamic module for the stage.
     */
    public function atEmail(string $email)
    {
        return $this->state(fn () => ['email' => $email]);
    }
}
