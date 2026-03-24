<?php

namespace ApurbaLabs\ApprovalEngine\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WorkflowDatabaseSeeder::class, 
        ]);
    }
}
