<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PriceChangeNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $priceChange;

    public $changePercentage;

    /**
     * Create a new message instance.
     *
     * @param mixed      $ticket
     * @param mixed      $oldPrice
     * @param mixed      $newPrice
     * @param mixed      $user
     * @param mixed|null $platform
     */
    public function __construct(public $ticket, public $oldPrice, public $newPrice, public $user, public $platform = NULL)
    {
        $this->priceChange = $this->newPrice - $this->oldPrice;
        $this->changePercentage = $this->oldPrice > 0 ? (($this->newPrice - $this->oldPrice) / $this->oldPrice) * 100 : 0;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->priceChange > 0 ? 'Price Increase Alert' : 'Price Drop Alert';

        return new Envelope(
            subject: $subject . ' - ' . $this->ticket->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.price-change-notification',
            with: [
                'ticket'           => $this->ticket,
                'oldPrice'         => $this->oldPrice,
                'newPrice'         => $this->newPrice,
                'priceChange'      => $this->priceChange,
                'changePercentage' => $this->changePercentage,
                'user'             => $this->user,
                'platform'         => $this->platform,
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
}
