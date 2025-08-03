<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the admin sidebar menu, icons, routes, and permissions.
    |
    */
    'sidebar' => [
        'brand' => [
            'text' => 'Master Admin',
            'logo' => 'vendor/master-admin/assets/img/logoIT.png',
            'logo_mini' => 'vendor/master-admin/assets/img/logoIT.png',
            'route' => 'admin.dashboard',
        ],
        'theme' => [
            'dark_mode' => false,
            'class' => 'bg-body-tertiary shadow',
        ],
        'menu' => [
            [
                'text' => 'Dashboard',
                'icon' => 'bi bi-speedometer',
                'route' => 'admin.dashboard',
                'active' => 'admin.dashboard',
            ],
            [
                'text' => 'Cấu hình',
                'icon' => 'bi bi-gear',
                'active' => 'admin/config-ai*',
                'submenu' => [
                    [
                        'text' => 'Storage AI',
                        'icon' => 'bi bi-hdd-network',
                        'route' => 'admin.config-ai.storage-ai.index',
                        'active' => 'admin/config-ai/storage-ai*',
                    ],
                ],
            ],

        ],
    ],
];
