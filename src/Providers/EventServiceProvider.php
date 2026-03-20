<?php

namespace ApurbaLabs\ApprovalEngine\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Events\BatchApproved;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Events\WorkflowRejected;
use ApurbaLabs\ApprovalEngine\Listeners\HandleWorkflowStarted;
use ApurbaLabs\ApprovalEngine\Listeners\HandleBatchApproval;
use ApurbaLabs\ApprovalEngine\Listeners\HandleWorkflowCompletion;
use ApurbaLabs\ApprovalEngine\Listeners\HandleWorkflowRejection;

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
            HandleBatchApproval::class,
        ],
        WorkflowCompleted::class => [
            HandleWorkflowCompletion::class,
        ],
        WorkflowRejected::class => [
            HandleWorkflowRejection::class,
        ],
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
