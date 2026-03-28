<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Expense;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'total_amount' => $faker->randomFloat(2, 100, 20000),
            'user_id'      => 1,
            'created_by'   => null,
            'admin_id'     => null,
            'status'       => 'pending',
            'approved_at'  => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }
}
