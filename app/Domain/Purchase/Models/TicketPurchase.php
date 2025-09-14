<?php declare(strict_types=1);

namespace App\Domain\Purchase\Models;

use App\Models\TicketPurchase as BaseTicketPurchase;

/**
 * Shim class to maintain backward compatibility with domain namespace.
 * Extends the actual Eloquent model at App\Models\TicketPurchase.
 */
class TicketPurchase extends BaseTicketPurchase
{
}
