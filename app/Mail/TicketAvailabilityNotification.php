<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAvailabilityNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;
    public $oldStatus;
    public $newStatus;
    public $user;
    public $platform;
    public $quantity;

    /**
     * Create a new message instance.
     */
    public function __construct($ticket, $oldStatus, $newStatus, $user, $platform = null, $quantity = null)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->user = $user;
        $this->platform = $platform ?: $ticket['platform'] ?? 'Unknown';
        $this->quantity = $quantity;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectLine();
        
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            tags: ['availability-change', 'ticket-alert'],
            metadata: [
                'ticket_id' => $this->ticket['id'] ?? null,
                'platform' => $this->platform,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'user_id' => $this->user['id'] ?? null,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-availability-notification',
            text: 'emails.ticket-availability-notification-text',
            with: [
                'ticket' => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'user' => $this->user,
                'platform' => $this->platform,
                'quantity' => $this->quantity,
                'isNowAvailable' => $this->newStatus === 'available' && $this->oldStatus !== 'available',
                'isSoldOut' => $this->newStatus === 'sold_out',
                'isLimitedQuantity' => $this->quantity && $this->quantity <= 10,
                'statusMessage' => $this->getStatusMessage(),
                'urgency' => $this->getUrgencyLevel(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Generate appropriate subject line based on status change
     */
    private function getSubjectLine(): string
    {
        $eventName = $this->ticket['event_name'] ?? 'Event';
        
        if ($this->newStatus === 'available' && $this->oldStatus !== 'available') {
            return "🎉 Tickets Available: {$eventName}";
        } elseif ($this->newStatus === 'sold_out') {
            return "⚠️ Sold Out: {$eventName}";
        } elseif ($this->newStatus === 'limited') {
            return "⏰ Limited Tickets: {$eventName}";
        } else {
            return "🎫 Status Update: {$eventName}";
        }
    }

    /**
     * Get human-readable status message
     */
    private function getStatusMessage(): string
    {
        switch ($this->newStatus) {
            case 'available':
                return 'Tickets are now available for purchase!';
            case 'sold_out':
                return 'All tickets have been sold out.';
            case 'limited':
                return 'Only a few tickets remaining!';
            case 'presale':
                return 'Tickets are available for presale members.';
            case 'not_available':
                return 'Tickets are currently not available.';
            default:
                return 'Ticket status has been updated.';
        }
    }

    /**
     * Determine urgency level for styling
     */
    private function getUrgencyLevel(): string
    {
        if ($this->newStatus === 'available' && $this->oldStatus !== 'available') {
            return 'high'; // Just became available
        } elseif ($this->newStatus === 'limited' || ($this->quantity && $this->quantity <= 10)) {
            return 'medium'; // Limited quantity
        } elseif ($this->newStatus === 'sold_out') {
            return 'low'; // Sold out (informational)
        }
        
        return 'normal';
    }
}
