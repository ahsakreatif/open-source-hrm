<?php

namespace App\Providers;

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
        // Add Inertia middleware to web group
        // Commented out due to Laravel 12 middleware configuration changes
        // $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\HandleInertiaRequests::class);
    }
}
