<?php

namespace ApurbaLabs\ApprovalEngine;

use Illuminate\Support\ServiceProvider;

class ApprovalEngineServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'approval-engine');

        $this->publishes([
            __DIR__.'/../config/approval-engine.php' => config_path('approval-engine.php'),
        ], 'approval-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/approval-engine'),
        ], 'approval-views');
    }

    public function register()
    {
        $this->commands([
            \ApurbaLabs\ApprovalEngine\Console\InstallCommand::class
        ]);
    }

}
