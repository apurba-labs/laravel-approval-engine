<?php
namespace ApurbaLabs\ApprovalEngine\Database\Seeders;
//namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run()
    {
        // Roles

        DB::table('roles')->updateOrInsert(
            ['name' => 'HOSD'],
            ['description' => 'Head of Sales & distribution']
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'COO'],
            ['description' => 'Chief Operating Officer']
        );

        // Workflow settings
        DB::table('workflow_stages')->insert([
            [
                'module' => 'requisition',
                'stage_order' => 1,
                'role' => 'HOSD',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'module' => 'requisition',
                'stage_order' => 2,
                'role' => 'COO',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
