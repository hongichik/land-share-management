<?php

namespace Hongdev\MasterAdmin;

use Illuminate\Support\ServiceProvider;
use Hongdev\MasterAdmin\Http\Middleware\MasterAdminMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MasterAdminServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/masteradmin.php', 'masteradmin');
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'master-admin');

        // Publish assets
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/master-admin'),
            __DIR__ . '/../public/favicon.ico' => public_path('favicon.ico'),
        ], 'master-admin-public');

        $this->publishes([
            __DIR__ . '/../www' => base_path(),
        ], 'master-admin-environment');

        // Publish Auth system files
        $this->publishes([
            __DIR__ . '/../Export/Auth/views' => resource_path('views'),
            __DIR__ . '/../Export/Auth/Controllers' => app_path('Http/Controllers'),
            __DIR__ . '/../Export/Auth/Middleware' => app_path('Http/Middleware'),
            __DIR__ . '/../Export/Auth/Models' => app_path('Models'),
            __DIR__ . '/../Export/Auth/routes' => base_path('routes'),
            __DIR__ . '/../Export/Auth/seeders' => database_path('seeders'),
            __DIR__ . '/../Export/Auth/Providers' => app_path('Providers'),
            __DIR__ . '/../Export/Auth/migrations' => database_path('migrations'),
            __DIR__ . '/../Export/Auth/config' => config_path(),
        ], 'master-admin-auth');


        // Đăng ký middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('master-admin', MasterAdminMiddleware::class);

        // Ensure the middleware group has session and error handling
        $router->middlewareGroup('master-admin', [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Hongdev\MasterAdmin\Http\Middleware\MasterAdminMiddleware::class,
        ]);

        // Register Google Drive disk configuration
        $this->registerGoogleDriveDisk();

        // Register Google Drive storage driver
        $this->registerGoogleDriveDriver();
    }

    protected function registerGoogleDriveDisk()
    {
        $googleConfig = config('masteradmin.google');

        // Add google disk to filesystems config
        config([
            'filesystems.disks.google' => [
                'driver' => 'google',
                'clientId' => $googleConfig['clientId'],
                'clientSecret' => $googleConfig['clientSecret'],
                'refreshToken' => $googleConfig['refreshToken'],
                'folder' => $googleConfig['folder'],
            ]
        ]);
    }

    protected function registerGoogleDriveDriver()
    {
        try {
            Storage::extend('google', function ($app, $config) {
                $options = [];

                if (! empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                $client = new \Google\Client;
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                // Always get fresh access token instead of using config
                try {
                    $googleConfig = config('masteradmin.google');

                    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://oauth2.googleapis.com/token', [
                        'client_id' => $googleConfig['clientId'],
                        'client_secret' => $googleConfig['clientSecret'],
                        'refresh_token' => $googleConfig['refreshToken'],
                        'grant_type' => 'refresh_token'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $client->setAccessToken($data['access_token']);
                        Log::info('Fresh Google Drive access token obtained');
                    } else {
                        Log::error('Failed to get fresh Google Drive token: ' . $response->body());
                        // Fallback to config token if available
                        if (isset($config['accessToken'])) {
                            $client->setAccessToken($config['accessToken']);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error getting fresh Google Drive token: ' . $e->getMessage());
                    // Fallback to config token if available
                    if (isset($config['accessToken'])) {
                        $client->setAccessToken($config['accessToken']);
                    }
                }

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
