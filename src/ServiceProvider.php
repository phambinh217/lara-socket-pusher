<?php

namespace Phambinh\LaraSocketPusher;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ .'/../config/pusher.php' => config_path('pusher.php')
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton('lara-socket-pusher', \Phambinh\LaraSocketPusher\Services\Pusher::class);

        $this->commands([
            \Phambinh\LaraSocketPusher\Commands\PusherServeCommand::class
        ]);
    }
}