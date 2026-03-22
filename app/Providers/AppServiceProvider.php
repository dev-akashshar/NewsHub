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
        view()->composer('*', function ($view) {
            try {
                $appName = \App\Models\Setting::get('app_name', config('app.name'));
                config(['app.name' => $appName]);
            } catch (\Exception $e) {
                // DB not ready yet (during migrations)
            }
        });
    }
}