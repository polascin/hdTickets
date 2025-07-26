<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class TicketApiFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                true, // Allow inline line breaks
                true  // Ignore empty context and extra
            ));
        }
    }
}
