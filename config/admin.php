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
                'text' => 'Quản lý đất đai',
                'icon' => 'bi bi-house',
                'active' => 'admin/land-rental-contracts*',
                'submenu' => [
                    [
                        'text' => 'Hợp đồng thuê đất',
                        'icon' => 'bi bi-file-earmark-text',
                        'route' => 'admin.land-rental-contracts.index',
                        'active' => 'admin/land-rental-contracts*',
                    ],
                ],
            ],
            [
                'text' => 'Quản lý Setting',
                'icon' => 'bi bi-gear',
                'route' => 'admin.settings.index',
                'active' => 'admin/settings*',
            ],
            [
                'text' => 'Quản lý chứng khoán',
                'icon' => 'bi bi-graph-up',
                'active' => 'admin/securities/*',
                'submenu' => [
                    [
                        'text' => 'Cổ đông',
                        'icon' => 'bi bi-file-earmark-text',
                        'route' => 'admin.securities.management.index',
                        'active' => 'admin/securities/management*',
                    ],
                    [
                        'text' => 'Quản lý cổ tức',
                        'icon' => 'bi bi-bar-chart',
                        'route' => 'admin.securities.dividend.index',
                        'active' => 'admin/securities/dividend*',
                    ],
                    [
                        'text' => 'Danh sách trả cổ tức',
                        'icon' => 'bi bi-receipt',
                        'route' => 'admin.securities.dividend-record.index',
                        'active' => 'admin/securities/dividend-record*',
                    ],
                ],
            ],
        ],
    ],
];
