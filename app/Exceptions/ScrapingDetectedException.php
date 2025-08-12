<?php

namespace App\Exceptions;

/**
 * Scraping detection exception - when anti-bot measures are triggered
 */
class ScrapingDetectedException extends TicketPlatformException
{
    public function __construct($message = "", $platform = null)
    {
        parent::__construct($message, 403, null, $platform, 'scraping');
    }
}
