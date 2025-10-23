<?php declare(strict_types=1);

namespace App\Observers;

use App\Models\ScrapedTicket;
use Illuminate\Support\Facades\Cache;

class ScrapedTicketObserver
{
    public function created(ScrapedTicket $ticket): void
    {
        $this->flushStats();
    }

    public function updated(ScrapedTicket $ticket): void
    {
        $this->flushStats();
    }

    public function deleted(ScrapedTicket $ticket): void
    {
        $this->flushStats();
    }

    private function flushStats(): void
    {
        Cache::forget('stats:available_tickets');
        Cache::forget('stats:new_today');
        Cache::forget('stats:unique_events');
        Cache::forget('stats:average_price');
    }
}
