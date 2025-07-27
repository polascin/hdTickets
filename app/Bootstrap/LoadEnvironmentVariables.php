<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class LoadEnvironmentVariables
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $application
     * @return void
     */
    public function bootstrap(Application $application)
    {
        // Environment variables are already loaded in bootstrap/app.php
        // This bypasses Laravel's default .env loading that uses disabled functions
    }
}
