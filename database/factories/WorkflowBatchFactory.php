<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowBatchFactory extends Factory
{
    protected $model = WorkflowBatch::class;

    public function definition()
    {
        return [
            'module'       => 'PurchaseWorkflow',
            'role'         => 'HOSD',
            'token'        => 'secure-token-123',
            'window_start' => now()->subHour(),
            'window_end'   => now(),
            'status'       => 'pending',
            'stage'        => 1,
        ];
    }
}
