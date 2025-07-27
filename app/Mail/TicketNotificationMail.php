<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $notification;

    public function __construct(User $user, array $notification)
    {
        $this->user = $user;
        $this->notification = $notification;
    }

    public function build()
    {
        $subject = $this->notification['title'];
        
        // Add CSS cache busting timestamp to subject for tracking
        $cssTimestamp = $this->notification['data']['css_timestamp'] ?? now()->timestamp;
        
        return $this->subject($subject)
                    ->view('emails.ticket-notification')
                    ->with([
                        'user' => $this->user,
                        'notification' => $this->notification,
                        'cssTimestamp' => $cssTimestamp,
                    ]);
    }
}
