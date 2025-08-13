<?php declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $user;

    public $changeDetails;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->changeDetails = [
            'changed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location'   => $this->getLocationFromIP(request()->ip()),
        ];
    }

    /**
     * Get the message envelope.
     */
    /**
     * Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Changed - HD Tickets',
        );
    }

    /**
     * Get the message content definition.
     */
    /**
     * Content
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.password-changed',
            text: 'emails.password-changed-text',
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
     * Get approximate location from IP address
     */
    /**
     * Get  location from i p
     */
    private function getLocationFromIP(string $ip): string
    {
        // In a production environment, you might use a service like:
        // - MaxMind GeoLite2
        // - IP-API
        // - ipinfo.io

        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local Development';
        }

        // For now, just return the IP
        return "IP: {$ip}";
    }
}
