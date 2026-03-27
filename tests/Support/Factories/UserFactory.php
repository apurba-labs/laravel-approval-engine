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
     * Fix: Combined forName and matching email logic.
     * This ensures the email is always unique based on the name.
     */
    public function forName(?string $name = null, string $domain = 'test.com'): self
    {
        return $this->state(function () use ($name, $domain) {
            // Generate a random name if none provided, or use the one passed
            $finalName = $name ?? $this->faker->unique()->firstName() . '_' . $this->faker->randomNumber(2);
            
            return [
                'name'  => $finalName,
                'email' => strtolower($finalName) . '@' . $domain,
            ];
        });
    }

    /**
     * Assign a role to the user, creating it only if it doesn't exist.
     */
    public function withRole(string $roleName, ?string $description = null): self
    {
        return $this->state(function () use ($roleName, $description) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $description ?? "System generated {$roleName} role"]
            );

            return [
                'role_id' => $role->id,
            ];
        });
    }

    /**
     * Keep this for cases where you need a very specific email regardless of name.
     */
    public function atEmail(string $email): self
    {
        return $this->state(['email' => $email]);
    }
}
