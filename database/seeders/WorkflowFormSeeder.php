<?php

namespace ApurbaLabs\ApprovalEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\{WorkflowForm, WorkflowModule};

class WorkflowFormSeeder extends Seeder
{
    public function run(): void
    {
        WorkflowForm::factory()
            ->for(WorkflowModule::factory()->state([
                'name' => 'Expense',
                'slug' => 'expense',
            ]))
            ->create([
                'version' => 1,
                'is_active' => true,
                'schema' => [
                    'fields' => [
                        ['name' => 'amount', 'type' => 'number', 'required' => true],
                    ]
                ],
            ]);
    }
}
