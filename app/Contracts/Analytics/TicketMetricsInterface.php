<?php

declare(strict_types=1);

namespace App\Contracts\Analytics;

interface TicketMetricsInterface
{
    /**
     * Record a price change for ticket analytics
     */
    public function recordPriceChange(string $ticketId, mixed $oldPrice, mixed $newPrice): void;

    /**
     * Record an availability change for ticket analytics
     */
    public function recordAvailabilityChange(string $ticketId, mixed $oldStatus, mixed $newStatus): void;
}
