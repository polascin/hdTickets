<?php declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletionRequested extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $confirmationToken,
    ) {
    }

    public function build()
    {
        return $this->subject('Account Deletion Requested')
            ->view('emails.account-deletion-requested')
            ->with([
                'user'            => $this->user,
                'confirmationUrl' => url('/account/delete/confirm/' . $this->confirmationToken),
            ]);
    }
}
