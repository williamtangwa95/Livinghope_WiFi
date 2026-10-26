<?php

namespace App\Providers;

use App\Contracts\HotspotGatewayInterface;
use App\Services\Gateway\GatewayManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GatewayManager::class, function ($app) {
            return new GatewayManager(config('hotspot', []));
        });

        $this->app->bind(HotspotGatewayInterface::class, function ($app) {
            return $app->make(GatewayManager::class)->driver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
