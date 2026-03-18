<?php

namespace ApurbaLabs\ApprovalEngine;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Listeners\SendBatchApprovalNotification;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Listeners\NotifyWorkflowCompletion;

use ApurbaLabs\ApprovalEngine\Console\InstallCommand;
use ApurbaLabs\ApprovalEngine\Console\SendWorkflowBatchCommand;
use ApurbaLabs\ApprovalEngine\Console\MakeWorkflowModule;
use ApurbaLabs\ApprovalEngine\Console\WorkflowVisualizerCommand;
use ApurbaLabs\ApprovalEngine\Console\BatchStatusCommand;

class ApprovalEngineServiceProvider extends ServiceProvider
{
    protected $listen = [
        BatchApproved::class => [
            SendBatchApprovalNotification::class,
        ],
        WorkflowCompleted::class => [
            NotifyWorkflowCompletion::class,
        ],
    ];

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

        Route::prefix('approval')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/approval.php');
        });

        // Register the listeners in a package
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    public function register()
    {
        $this->commands([
            InstallCommand::class,
            SendWorkflowBatchCommand::class,
            MakeWorkflowModule::class,
            WorkflowVisualizerCommand::class,
            BatchStatusCommand::class,
        ]);
    }

}
