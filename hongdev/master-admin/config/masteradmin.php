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
            'route' => 'master-admin.dashboard',
        ],
        'theme' => [
            'dark_mode' => false,
            'class' => 'bg-body-tertiary shadow',
        ],
        'menu' => [
            [
                'text' => 'Dashboard',
                'icon' => 'bi bi-speedometer',
                'route' => 'master-admin.dashboard',
                'active' => 'master-admin',
            ],
            [
                'text' => 'Settings',
                'icon' => 'bi bi-gear-fill',
                'active' => 'master-admin/settings*',
                'submenu' => [
                    [
                        'text' => 'Environment',
                        'icon' => 'bi bi-server',
                        'route' => 'master-admin.settings.environment.index',
                        'active' => 'master-admin/settings/environment*',
                    ],
                    [
                        'text' => 'Database',
                        'icon' => 'bi bi-database-fill',
                        'route' => 'master-admin.settings.database.index',
                        'active' => 'master-admin/settings/database*',
                    ],
                    [
                        'text' => 'Mail Configuration',
                        'icon' => 'bi bi-envelope',
                        'route' => 'master-admin.settings.mail.index',
                        'active' => 'master-admin/settings/mail*',
                    ],
                    [
                        'text' => 'Google Drive',
                        'icon' => 'bi bi-cloud-arrow-up',
                        'route' => 'master-admin.settings.drive.index',
                        'active' => 'master-admin/settings/drive*',
                    ],
                ],
            ],
            [
                'text' => 'Code Generator',
                'icon' => 'bi bi-code-slash',
                'active' => 'master-admin/code-generator*',
                'submenu' => [
                    [
                        'text' => 'Auth Generator',
                        'icon' => 'bi bi-person-fill',
                        'route' => 'master-admin.code-generator.auth.index',
                        'active' => 'master-admin/code-generator/auth*',
                    ],
                    // Add more code generators here
                ],
            ],
            [
                'text' => 'File Manager',
                'icon' => 'bi bi-folder',
                'route' => 'master-admin.file-manager.index',
                'active' => 'master-admin/file-manager*',
            ],
            [
                'text' => 'Logs',
                'icon' => 'bi bi-file-text-fill',
                'route' => 'master-admin.logs.view',
                'active' => 'master-admin/logs*',
            ],
            [
                'text' => 'Backup',
                'icon' => 'bi bi-hdd-stack',
                'route' => 'master-admin.backup.index',
                'active' => 'master-admin/backup*',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the admin route prefix and middleware.
    |
    */
    'route' => [
        'prefix' => 'master-admin',
        'middleware' => ['web', 'master-admin'],
    ],

    'google' => [
        'driver' => 'google',
        'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'folder' => env('GOOGLE_DRIVE_FOLDER'),
    ]
];
