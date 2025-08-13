<?php declare(strict_types=1);

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class LoadEnvironmentVariables
{
    /**
     * Bootstrap the given application.
     */
    /**
     * Bootstrap
     */
    public function bootstrap(Application $application): void
    {
        // Environment variables are already loaded in bootstrap/app.php
        // This bypasses Laravel's default .env loading that uses disabled functions
    }
}
