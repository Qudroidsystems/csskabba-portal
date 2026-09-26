<?php

namespace App\Providers;

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
        // Module feature flags: @feature('key') ... @endfeature (combine with @can).
        if (class_exists(\App\Models\FeatureFlag::class)) {
            Blade::if('feature', fn (string $key) => \App\Models\FeatureFlag::enabled($key));
            Blade::if('featureany', fn (array $keys) => \App\Models\FeatureFlag::anyEnabled($keys));
        }
    }
}
