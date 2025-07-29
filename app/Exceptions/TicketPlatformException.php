<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception class for all ticket platform related errors
 */
class TicketPlatformException extends Exception
{
    protected $platform;
    protected $method; // 'api' or 'scraping'
    
    public function __construct($message = "", $code = 0, Exception $previous = null, $platform = null, $method = null)
    {
        parent::__construct($message, $code, $previous);
        $this->platform = $platform;
        $this->method = $method;
    }
    
    public function getPlatform()
    {
        return $this->platform;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
}

/**
 * Platform-specific exception classes
 */
class TicketmasterException extends TicketPlatformException {}

class SeatGeekException extends TicketPlatformException {}

class StubHubException extends TicketPlatformException {}

class EventbriteException extends TicketPlatformException {}

class BandsinTownException extends TicketPlatformException {}

class ViagogoException extends TicketPlatformException {}

class TickPickException extends TicketPlatformException {}

class FunZoneException extends TicketPlatformException {}

/**
 * Rate limit specific exception
 */
class RateLimitException extends TicketPlatformException
{
    protected $retryAfter;
    
    public function __construct($message = "", $retryAfter = null, $platform = null)
    {
        parent::__construct($message, 429, null, $platform);
        $this->retryAfter = $retryAfter;
    }
    
    public function getRetryAfter()
    {
        return $this->retryAfter;
    }
}

/**
 * Timeout specific exception
 */
class TimeoutException extends TicketPlatformException
{
    public function __construct($message = "", $platform = null, $method = null)
    {
        parent::__construct($message, 408, null, $platform, $method);
    }
}

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

