<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Support\Str;

class WorkflowBatchFactory extends Factory
{
    protected $model = WorkflowBatch::class;

    public function definition(): array
    {
        return [
            'module' => 'purchase',
            'role' => 'HOD',
            'token' => Str::random(32),
            'stage' => 1,
            'status' => 'sent',
            'window_start' => now()->subDay(),
            'window_end' => now(),
        ];
    }
}
