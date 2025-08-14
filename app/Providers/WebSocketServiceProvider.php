<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;

class WebSocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    /**
     * Boot
     */
    public function boot(BroadcastManager $broadcast): void
    {
        $broadcast->routes();
        require base_path('routes/channels.php');
    }

    /**
     * Register services.
     */
    /**
     * Register
     */
    public function register(): void
    {
        $this->app->singleton('WebSocketServer', function ($app) {
            return new \BeyondCode\LaravelWebSockets\WebSocketsServiceProvider($app);
        });
    }
}
