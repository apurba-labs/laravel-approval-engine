<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'approval-engine:install';

    protected $description = 'Install and initialize the Laravel Approval Engine';

    public function handle()
    {
        $this->info('Installing Laravel Approval Engine...');

        $this->info('Publishing assets...');
        $this->call('vendor:publish', [
            '--tag' => 'approval-config'
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'approval-views'
        ]);

        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
        }

        if ($this->confirm('Do you want to seed the default workflow stages?', false)) {
            $this->info('Seeding database...');
            $this->call('db:seed', [
                '--class' => WorkflowSeeder::class
            ]);
        }

        $this->info('Installation completed successfully!');
    }
}
