<?php declare(strict_types=1);

namespace App\Observers;

use App\Models\TicketAlert;
use Illuminate\Support\Facades\Cache;

class TicketAlertObserver
{
    public function created(TicketAlert $alert): void
    {
        $this->flushUserAlertCache($alert);
    }

    public function updated(TicketAlert $alert): void
    {
        $this->flushUserAlertCache($alert);
    }

    public function deleted(TicketAlert $alert): void
    {
        $this->flushUserAlertCache($alert);
    }

    private function flushUserAlertCache(TicketAlert $alert): void
    {
        if ($alert->user_id) {
            Cache::forget("stats:user:{$alert->user_id}:active_alerts");
        }
    }
}
