<?php declare(strict_types=1);

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ErrorTrackingLogger
{
    /**
     * Create a custom Monolog instance
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('error-tracking');
        $logger->pushHandler(new StreamHandler(
            storage_path('logs/error-tracking.log'),
            Logger::ERROR,
        ));

        return $logger;
    }
}
