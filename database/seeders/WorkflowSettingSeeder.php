<?php

namespace ApurbaLabs\ApprovalEngine\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('workflow_settings')->insert([
               [
                'module' => 'requisition',
                'role' => 'HOFA',
                'frequency' => 'daily',
                'weekly_day' => null, //Sunday
                'monthly_date' => null,
                'send_time' => '09:00:00',
                'timezone' => 'Asia/Dhaka',
                'last_run_at' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module' => 'requisition',
                'role' => 'HOSD',
                'frequency' => 'weekly',
                'weekly_day' => 0, //Sunday
                'monthly_date' => null,
                'send_time' => '09:00:00',
                'timezone' => 'Asia/Dhaka',
                'last_run_at' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module' => 'requisition',
                'role' => 'COO',
                'frequency' => 'monthly',
                'weekly_day' => null,
                'monthly_date' => 1, // 1st of the month
                'send_time' => '08:00:00',
                'timezone' => 'Asia/Dhaka',
                'last_run_at' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
