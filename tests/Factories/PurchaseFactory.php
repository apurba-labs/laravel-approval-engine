<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ApurbaLabs\ApprovalEngine\Tests\Models\Purchase;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'total_amount' => $this->faker->randomFloat(2, 100, 20000),
            'user_id'      => 1,
            'status'       => 'pending',
            'created_at'   => now(),
        ];
    }
}
