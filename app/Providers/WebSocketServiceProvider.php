<?php declare(strict_types=1);

namespace App\Providers;

use BeyondCode\LaravelWebSockets\WebSocketsServiceProvider;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use Override;

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
    #[Override]
    public function register(): void
    {
        $this->app->singleton('WebSocketServer', fn ($app): \WebSocketsServiceProvider => new WebSocketsServiceProvider($app));
    }
}
