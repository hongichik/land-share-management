<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();
        
        // Không force HTTPS nếu là localhost hoặc 127.0...
        if (!str_starts_with($host, 'localhost') && !str_starts_with($host, '127.0.0.1')) {
            URL::forceScheme('https');
        }
    }
}
