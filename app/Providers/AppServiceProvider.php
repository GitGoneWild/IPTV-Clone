<?php

namespace App\Providers;

use App\Services\XtreamService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(XtreamService::class, function ($app) {
            return new XtreamService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define admin gate
        Gate::define('admin', function ($user) {
            return $user->is_admin;
        });

        // Define reseller gate
        Gate::define('reseller', function ($user) {
            return $user->is_reseller || $user->is_admin;
        });
    }
}
