<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
        // Render pagination with the Bootstrap 5 theme (matches the Velzon UI).
        Paginator::useBootstrapFive();

        // Module feature flags: @feature('key') ... @endfeature (combine with @can).
        if (class_exists(\App\Models\FeatureFlag::class)) {
            Blade::if('feature', fn (string $key) => \App\Models\FeatureFlag::enabled($key));
            Blade::if('featureany', fn (array $keys) => \App\Models\FeatureFlag::anyEnabled($keys));
        }
    }
}
