<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PriceChangeNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;
    public $oldPrice;
    public $newPrice;
    public $priceChange;
    public $changePercentage;
    public $user;
    public $platform;

    /**
     * Create a new message instance.
     */
    public function __construct($ticket, $oldPrice, $newPrice, $user, $platform = null)
    {
        $this->ticket = $ticket;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->priceChange = $newPrice - $oldPrice;
        $this->changePercentage = $oldPrice > 0 ? round(($this->priceChange / $oldPrice) * 100, 2) : 0;
        $this->user = $user;
        $this->platform = $platform ?: $ticket['platform'] ?? 'Unknown';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $direction = $this->priceChange > 0 ? 'Increased' : 'Decreased';
        $subject = "🎫 Price {$direction}: {$this->ticket['event_name']}";
        
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
            tags: ['price-change', 'ticket-alert'],
            metadata: [
                'ticket_id' => $this->ticket['id'] ?? null,
                'platform' => $this->platform,
                'price_change' => $this->priceChange,
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
            view: 'emails.price-change-notification',
            text: 'emails.price-change-notification-text',
            with: [
                'ticket' => $this->ticket,
                'oldPrice' => $this->oldPrice,
                'newPrice' => $this->newPrice,
                'priceChange' => $this->priceChange,
                'changePercentage' => $this->changePercentage,
                'user' => $this->user,
                'platform' => $this->platform,
                'isIncrease' => $this->priceChange > 0,
                'isSignificant' => abs($this->changePercentage) >= 10,
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
}
