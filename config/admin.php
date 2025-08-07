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
                'text' => 'Quản lý chứng khoán',
                'icon' => 'bi bi-graph-up',
                'active' => 'admin/securities/*',
                'submenu' => [
                    [
                        'text' => 'Thông tin quản lý chứng khoán',
                        'icon' => 'bi bi-file-earmark-text',
                        'route' => 'admin.securities.management.index',
                        'active' => 'admin/securities/management*',
                    ],
                    [
                        'text' => 'Lịch sử thanh toán cổ tức',
                        'icon' => 'bi bi-cash-coin',
                        'route' => 'admin.securities.history.index',
                        'active' => 'admin/securities/history*',
                    ],
                    [
                        'text' => 'Tạo thanh toán cổ tức',
                        'icon' => 'bi bi-plus-circle',
                        'route' => 'admin.securities.history.create',
                        'active' => 'admin/securities/history/create',
                    ],
                ],
            ],
        ],
    ],
];
