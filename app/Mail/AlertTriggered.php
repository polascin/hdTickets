<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertTriggered extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $data = [],
    ) {
    }

    public function build()
    {
        return $this->subject('Alert Triggered')
            ->view('emails.alert.triggered')
            ->with($this->data);
    }
}
