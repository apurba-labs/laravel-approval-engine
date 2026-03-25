<?php
namespace ApurbaLabs\ApprovalEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use ApurbaLabs\ApprovalEngine\Models\{WorkflowStage, WorkflowSetting, WorkflowRule, WorkflowInstance};

class WorkflowDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $userModel = config('auth.providers.users.model');
        // 1. Create Core Users & Roles
        $hosd = $userModel::factory()->withRole('HOSD', 'Head of Sales Distribution')->create(['name' => 'hosd', 'email' => 'hosd@test.com']);
        $coo  = $userModel::factory()->withRole('COO', 'Chief operations officer')->create(['name' => 'coo', 'email' => 'coo@test.com']);
        $md   = $userModel::factory()->withRole('MD', 'Managing Director')->create(['name' => 'md', 'email' => 'md@test.com']);

        // 2. Create requisition, Purchase Module Stages (HOSD -> COO -> MD)
        $stages = [
            ['module' => 'requisition', 'role' => 'HOSD', 'stage_order' => 1],
            ['module' => 'requisition', 'role' => 'COO', 'stage_order' => 2],
            ['module' => 'requisition', 'role' => 'MD', 'stage_order' => 3],
            ['module' => 'purchase', 'role' => 'HOSD', 'stage_order' => 1],
            ['module' => 'purchase', 'role' => 'COO', 'stage_order' => 2],
            ['module' => 'purchase', 'role' => 'MD', 'stage_order' => 3],
        ];

        foreach ($stages as $stage) {
            WorkflowStage::create($stage);
            
            // 3. Create Settings (Instant for HOSD, Daily for others)
            WorkflowSetting::create([
                'module' => $stage['module'],
                'role' => $stage['role'],
                'frequency' => ($stage['role'] === 'HOSD') ? 'instant' : 'daily',
                'is_active' => true
            ]);
        }

        // 4. Create a "High Value" Rule (If > 10k, go to MD)

        WorkflowRule::create([
            'module' => 'requisition',
            'field' => 'total_amount',
            'operator' => '>',
            'value' => '10000',
            'role' => 'MD',
            'priority' => 10
        ]);
        WorkflowRule::create([
            'module' => 'purchase',
            'field' => 'total_amount',
            'operator' => '>',
            'value' => '10000',
            'role' => 'MD',
            'priority' => 10
        ]);

        // 5. Optional: Create 1 or 2 "Live" Instance for Dashboard Testing
        WorkflowInstance::factory()->create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'payload' => ['total_amount' => 1200]
        ]);
        WorkflowInstance::factory()->create([
            'module' => 'purchase',
            'role' => 'HOSD',
            'payload' => ['total_amount' => 1200]
        ]);
    }
}
