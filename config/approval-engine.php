<?php

return [

    'test_email' => 'apurbansingh@yahoo.com',
    'reminder_days' => 2,
    'modules_path' => app_path('Workflow/Modules'),
    'modules_namespace' => 'App\\Workflow\\Modules\\',
    'notification' => [
        'default_channel' => 'mail',
        'channels' => [
            'mail' => true,
            'slack' => false,
        ],
    ],


];
