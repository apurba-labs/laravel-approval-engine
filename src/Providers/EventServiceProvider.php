<?php

namespace ApurbaLabs\ApprovalEngine\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Events\WorkflowRejected;
use ApurbaLabs\ApprovalEngine\Listeners\HandleWorkflowStarted;
use ApurbaLabs\ApprovalEngine\Listeners\HandleBatchApproved;
use ApurbaLabs\ApprovalEngine\Listeners\HandleWorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Listeners\HandleWorkflowRejected;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array
     */
    protected $listen = [
        WorkflowStarted::class => [
            HandleWorkflowStarted::class,
        ],
        BatchApproved::class => [
            HandleBatchApproved::class,
        ],
        WorkflowCompleted::class => [
            HandleWorkflowCompleted::class,
        ],
        //WorkflowRejected::class => [
        //    HandleWorkflowRejected::class,
        //],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
