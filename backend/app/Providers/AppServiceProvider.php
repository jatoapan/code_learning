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
        \App\Http\Middleware\Idempotency::addIdempotencyHeaderToResponse();

        // Allow Horizon access (Security is handled by HorizonBasicAuth middleware)
        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            \Laravel\Horizon\Horizon::auth(function ($request) {
                return true; 
            });
        }

        if (empty(config('jwt.secret'))) {
            config(['jwt.secret' => config('app.key')]);
        }
    }
}
