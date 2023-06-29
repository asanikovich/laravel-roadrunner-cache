<?php

declare(strict_types=1);

namespace ASanikovich\LaravelRoadRunnerCache;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Spiral\Goridge\RPC\RPCInterface;

class LaravelRoadRunnerCacheProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->publishes(
            [__DIR__.'/../config/roadrunner.php' => config_path('roadrunner.php')],
            'laravel-roadrunner-cache-config'
        );
    }

    public function boot(): void
    {
        $this->app->singleton(RPCInterface::class, static fn () => RoadRunnerFactory::createRPC());

        Cache::extend('roadrunner', function (Application $app, array $config) {
            /** @var RPCInterface $rpc */
            $rpc = $app->get(RPCInterface::class);
            $store = RoadRunnerFactory::createLaravelCacheStore($rpc, $config);

            return Cache::repository($store);
        });
    }
}
