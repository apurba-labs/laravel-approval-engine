<?php

namespace ApurbaLabs\ApprovalEngine\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'approval-engine:install';

    protected $description = 'Install Laravel Approval Engine';

    public function handle()
    {
        $this->info('Installing Laravel Approval Engine...');

        $this->call('vendor:publish', [
            '--tag' => 'approval-config'
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'approval-views'
        ]);

        $this->info('Installation completed.');
    }
}
