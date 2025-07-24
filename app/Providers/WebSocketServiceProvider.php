<?php

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;

class WebSocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(BroadcastManager $broadcast)
    {
        $broadcast->routes();
        require base_path('routes/channels.php');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('WebSocketServer', function ($app) {
            return new eyondcode\laravelwebsockets\WebSocketsServiceProvider($app);
        });
    }
}

