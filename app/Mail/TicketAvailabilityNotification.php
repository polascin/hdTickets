<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAvailabilityNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param mixed      $ticket
     * @param mixed      $oldStatus
     * @param mixed      $newStatus
     * @param mixed      $user
     * @param mixed|null $platform
     * @param mixed|null $quantity
     */
    public function __construct(public $ticket, public $oldStatus, public $newStatus, public $user, public $platform = NULL, public $quantity = NULL)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubjectLine(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-availability-notification',
            with: [
                'ticket'        => $this->ticket,
                'oldStatus'     => $this->oldStatus,
                'newStatus'     => $this->newStatus,
                'user'          => $this->user,
                'platform'      => $this->platform,
                'quantity'      => $this->quantity,
                'statusMessage' => $this->getStatusMessage(),
                'urgencyLevel'  => $this->getUrgencyLevel(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    /**
     * Attachments
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Generate appropriate subject line based on status change
     */
    /**
     * Get  subject line
     */
    private function getSubjectLine(): string
    {
        $eventName = $this->ticket['event_name'] ?? 'Event';

        if ($this->newStatus === 'available' && $this->oldStatus !== 'available') {
            return "🎉 Tickets Available: {$eventName}";
        }
        if ($this->newStatus === 'sold_out') {
            return "⚠️ Sold Out: {$eventName}";
        }
        if ($this->newStatus === 'limited') {
            return "⏰ Limited Tickets: {$eventName}";
        }

        return "🎫 Status Update: {$eventName}";
    }

    /**
     * Get human-readable status message
     */
    /**
     * Get  status message
     */
    private function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            'available'     => 'Tickets are now available for purchase!',
            'sold_out'      => 'All tickets have been sold out.',
            'limited'       => 'Only a few tickets remaining!',
            'presale'       => 'Tickets are available for presale members.',
            'not_available' => 'Tickets are currently not available.',
            default         => 'Ticket status has been updated.',
        };
    }

    /**
     * Determine urgency level for styling
     */
    /**
     * Get  urgency level
     */
    private function getUrgencyLevel(): string
    {
        if ($this->newStatus === 'available' && $this->oldStatus !== 'available') {
            return 'high'; // Just became available
        }
        if ($this->newStatus === 'limited' || ($this->quantity && $this->quantity <= 10)) {
            return 'medium'; // Limited quantity
        }
        if ($this->newStatus === 'sold_out') {
            return 'low'; // Sold out (informational)
        }

        return 'normal';
    }
}
