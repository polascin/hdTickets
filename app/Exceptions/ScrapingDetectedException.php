<?php declare(strict_types=1);

namespace App\Exceptions;

/**
 * Scraping detection exception - when anti-bot measures are triggered
 */
class ScrapingDetectedException extends TicketPlatformException
{
    public function __construct(string $message = '', ?string $platform = NULL)
    {
        parent::__construct($message, 403, NULL, $platform, 'scraping');
    }
}
