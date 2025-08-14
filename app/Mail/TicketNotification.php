<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketNotification extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $ticketData,
    ) {
    }

    public function build()
    {
        return $this->subject('Ticket Update Notification')
            ->view('emails.ticket-notification')
            ->with($this->ticketData);
    }
}
