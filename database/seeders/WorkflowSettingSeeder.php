<?php

namespace ApurbaLabs\ApprovalEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'module' => 'requisition',
                'role' => 'HOFA',
                'frequency' => 'daily',
                'weekly_day' => null,
                'send_time' => '09:00:00',
                'timezone' => 'Asia/Dhaka',
                'is_active' => true,
            ],
            [
                'module' => 'requisition',
                'role' => 'HOSD',
                'frequency' => 'weekly',
                'weekly_day' => 0, 
                'send_time' => '09:00:00',
                'timezone' => 'Asia/Dhaka',
                'is_active' => true,
            ],
            [
                'module' => 'requisition',
                'role' => 'COO',
                'frequency' => 'monthly',
                'monthly_date' => 1,
                'send_time' => '08:00:00',
                'timezone' => 'Asia/Dhaka',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('workflow_settings')->updateOrInsert(
                ['module' => $setting['module'], 'role' => $setting['role']], // Unique check
                array_merge($setting, ['updated_at' => now(), 'created_at' => now()]) // Values to update/insert
            );
        }
        
    }
}
