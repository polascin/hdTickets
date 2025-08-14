<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $purchaseData,
    ) {
    }

    public function build()
    {
        return $this->subject('Purchase Confirmation')
            ->view('emails.purchase-confirmation')
            ->with($this->purchaseData);
    }
}
