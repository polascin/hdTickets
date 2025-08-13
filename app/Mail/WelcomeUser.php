<?php declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUser extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {
    }

    public function build()
    {
        return $this->subject('Welcome to HD Tickets!')
            ->view('emails.welcome-user')
            ->with([
                'user'     => $this->user,
                'loginUrl' => url('/login'),
            ]);
    }
}
