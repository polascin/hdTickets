<?php declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base exception class for all ticket platform related errors
 */
class TicketPlatformException extends Exception
{
    // 'api' or 'scraping'

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = NULL, protected ?string $platform = NULL, protected ?string $method = NULL)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get  platform
     */
    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    /**
     * Get  method
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}

/**
 * Platform-specific exception classes
 */
class TicketmasterException extends TicketPlatformException
{
}

class SeatGeekException extends TicketPlatformException
{
}

class StubHubException extends TicketPlatformException
{
}

class EventbriteException extends TicketPlatformException
{
}

class BandsinTownException extends TicketPlatformException
{
}

class ViagogoException extends TicketPlatformException
{
}

class TickPickException extends TicketPlatformException
{
}

class FunZoneException extends TicketPlatformException
{
}

/**
 * Rate limit specific exception
 */
class RateLimitException extends TicketPlatformException
{
    public function __construct(string $message = '', protected ?int $retryAfter = NULL, ?string $platform = NULL)
    {
        parent::__construct($message, 429, NULL, $platform);
    }

    /**
     * Get  retry after
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}

/**
 * Timeout specific exception
 */
class TimeoutException extends TicketPlatformException
{
    public function __construct(string $message = '', ?string $platform = NULL, ?string $method = NULL)
    {
        parent::__construct($message, 408, NULL, $platform, $method);
    }
}
