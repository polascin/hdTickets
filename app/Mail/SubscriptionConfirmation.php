<?php declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly array $subscriptionData,
    ) {
    }

    public function build()
    {
        return $this->subject('Subscription Confirmation')
            ->view('emails.subscription-confirmation')
            ->with($this->subscriptionData);
    }
}
