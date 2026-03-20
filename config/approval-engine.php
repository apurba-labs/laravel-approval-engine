<?php

return [

    'test_email' => 'apurbansingh@yahoo.com',
    'reminder_days' => 2,
    'modules_path' => app_path('Workflow/Modules'),
    'modules_namespace' => 'App\\Workflow\\Modules\\',
    'stage_resolver' => ApurbaLabs\ApprovalEngine\Support\CustomStageResolver::class,
    'dynamic_stage_rules' => [
        \App\Workflows\PurchaseWorkflow::class => [
            'exception_column' => 'total_amount', 
            'trigger_threshold' => 10000,
            'role' => 'cfo',
        ],
        \App\Workflows\LeaveWorkflow::class => [
            'exception_column' => 'days_requested',
            'trigger_threshold' => 15,
            'role' => 'hr_head',
        ],
    ],

];
