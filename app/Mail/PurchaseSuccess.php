<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseSuccess extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $data = [],
    ) {
    }

    public function build()
    {
        return $this->subject('Purchase Successful')
            ->view('emails.purchase.success')
            ->with($this->data);
    }
}
