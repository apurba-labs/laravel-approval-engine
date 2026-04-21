<?php

namespace ApurbaLabs\ApprovalEngine;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

use ApurbaLabs\ApprovalEngine\Providers\EventServiceProvider as PackageEventServiceProvider;
use ApurbaLabs\IAM\Providers\IAMServiceProvider;
use ApurbaLabs\ApprovalEngine\Services\WorkflowManager;

use ApurbaLabs\ApprovalEngine\Console\InstallCommand;
use ApurbaLabs\ApprovalEngine\Console\SendWorkflowBatchCommand;
use ApurbaLabs\ApprovalEngine\Console\MakeWorkflowModule;
use ApurbaLabs\ApprovalEngine\Console\WorkflowVisualizerCommand;
use ApurbaLabs\ApprovalEngine\Console\BatchStatusCommand;
use ApurbaLabs\ApprovalEngine\Console\SetupApprovalDemo;
use ApurbaLabs\ApprovalEngine\Console\ProcessWorkflowNotifications;
use ApurbaLabs\ApprovalEngine\Services\PluginManager;
use ApurbaLabs\ApprovalEngine\Services\ModuleRegistry;

use ApurbaLabs\ApprovalEngine\Contracts\NotificationInterface;
use ApurbaLabs\ApprovalEngine\Services\NotificationService;

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

        $plugins = config('approval-engine.plugins', []);

        $manager = app(PluginManager::class);

        foreach ($plugins as $pluginClass) {
            $manager->register(app($pluginClass));
        }

        $manager->boot();
    }

    public function register()
    {
        
        $this->app->register(PackageEventServiceProvider::class);
        $this->app->register(IAMServiceProvider::class);

        $this->app->singleton(ModuleRegistry::class);

        $this->app->singleton(WorkflowManager::class);
        $this->app->singleton(PluginManager::class);

        $this->app->bind(
            NotificationInterface::class,
            NotificationService::class
        );
        
        $this->commands([
            InstallCommand::class,
            SendWorkflowBatchCommand::class,
            MakeWorkflowModule::class,
            WorkflowVisualizerCommand::class,
            BatchStatusCommand::class,
            SetupApprovalDemo::class,
            ProcessWorkflowNotifications::class,
        ]);
    }

}
