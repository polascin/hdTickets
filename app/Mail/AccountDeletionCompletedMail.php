<?php

namespace App\Mail;

use App\Models\User;
use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public AccountDeletionRequest $deletionRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, AccountDeletionRequest $deletionRequest)
    {
        $this->user = $user;
        $this->deletionRequest = $deletionRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Deletion Completed - HD Tickets',
            tags: ['account-deletion', 'completed'],
            metadata: [
                'user_id' => $this->user->id,
                'deletion_request_id' => $this->deletionRequest->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.account-deletion-completed',
            text: 'emails.account-deletion-completed-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
