<?php

namespace ApurbaLabs\ApprovalEngine;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

use ApurbaLabs\ApprovalEngine\Providers\EventServiceProvider as PackageEventServiceProvider;

use ApurbaLabs\ApprovalEngine\Console\InstallCommand;
use ApurbaLabs\ApprovalEngine\Console\SendWorkflowBatchCommand;
use ApurbaLabs\ApprovalEngine\Console\MakeWorkflowModule;
use ApurbaLabs\ApprovalEngine\Console\WorkflowVisualizerCommand;
use ApurbaLabs\ApprovalEngine\Console\BatchStatusCommand;
use ApurbaLabs\ApprovalEngine\Console\SetupApprovalDemo;
class ApprovalEngineServiceProvider extends ServiceProvider
{

    public function boot()
    {

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'approval-engine');

        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders')
        ], 'approval-seeders');

        $this->publishes([
            __DIR__.'/../config/approval-engine.php' => config_path('approval-engine.php'),
        ], 'approval-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/approval-engine'),
        ], 'approval-views');
        
        //$this->publishes([
        //    __DIR__.'/../database/seeders' => database_path('seeders')
        //], 'approval-seeders');

        $this->loadRoutesFrom(__DIR__.'/../routes/approval.php');
    }

    public function register()
    {
        
        $this->app->register(PackageEventServiceProvider::class);
        
        $this->commands([
            InstallCommand::class,
            SendWorkflowBatchCommand::class,
            MakeWorkflowModule::class,
            WorkflowVisualizerCommand::class,
            BatchStatusCommand::class,
            SetupApprovalDemo::class,
        ]);
    }

}
