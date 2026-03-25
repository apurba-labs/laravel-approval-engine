<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Requisition;

class RequisitionFactory extends Factory
{
    protected $model = Requisition::class;

    public function definition(): array
    {
        return [
            'total_amount' => $faker->randomFloat(2, 100, 20000),
            'reference_id' => Str::random(10),
            'user_id'      => 1,
            'status'       => 'pending',
            'approved_at'  => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }
}
