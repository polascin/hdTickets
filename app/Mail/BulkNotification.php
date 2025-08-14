<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkNotification extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $notificationData,
        public readonly array $recipients,
    ) {
    }

    public function build()
    {
        return $this->subject($this->notificationData['subject'] ?? 'Bulk Notification')
            ->view('emails.bulk-notification')
            ->with([
                'data'       => $this->notificationData,
                'recipients' => $this->recipients,
            ]);
    }
}
