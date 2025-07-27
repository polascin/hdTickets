<?php

// Set environment variables directly to bypass disabled functions
if (!function_exists('setEnvironmentVariables')) {
    function setEnvironmentVariables() {
        $env = [
            'APP_NAME' => 'HDTickets',
            'APP_ENV' => 'local',
            'APP_KEY' => 'base64:W4Zji6xRg6sxL7T6KHcrKGky7blZrP8kW+duKbrjkz4=',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost',
            'LOG_CHANNEL' => 'syslog',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'hdtickets',
            'DB_USERNAME' => 'hdtickets_user',
            'DB_PASSWORD' => 'hdtickets_pass',
            'SESSION_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'sync',
            'CACHE_DRIVER' => 'array',
        ];
        
        foreach ($env as $key => $value) {
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Set environment variables
setEnvironmentVariables();

// Create a dummy .env.example file to prevent Laravel from trying to load .env
if (!isset($_ENV['LARAVEL_ENV_LOADED'])) {
    $_ENV['LARAVEL_ENV_LOADED'] = 'true';
    putenv('LARAVEL_ENV_LOADED=true');
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'agent' => \App\Http\Middleware\AgentMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
