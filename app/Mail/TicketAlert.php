<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAlert extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $alertData = [],
    ) {
    }

    public function build()
    {
        return $this->subject('Ticket Alert Notification')
            ->view('emails.ticket-alert')
            ->with($this->alertData);
    }
}
