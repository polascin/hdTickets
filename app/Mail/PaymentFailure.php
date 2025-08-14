<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailure extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $data = [],
    ) {
    }

    public function build()
    {
        return $this->subject('Payment Failed')
            ->view('emails.payment.failure')
            ->with($this->data);
    }
}
