<?php

return [

    'modules' => [

        'requisition' => [

            'model' => App\Models\Requisition::class,

            'approved_column' => 'approved_at',

            'status_column' => 'stage_status',  // value must be 'Approve'
            
            // Relations to eager load
            'relations' => [
                'depot',
                'distributor',
                'shop'
            ],
            /*
            Fields needed internally (not shown in email)
            */
            'select_columns' => [
                'id',
                'reference_id',
                'depot_id',
                'distributor_id',
                'shop_id',
                'approved_at'
            ],
            /*
            Fields visible in notification
            */
            'display_columns' => [
                'reference_id' => 'Reference',
                'depot.name' => 'Depot',
                'distributor.name' => 'Distributor'
            ]

        ]

    ],
    'reminder_days' => 2,

];
