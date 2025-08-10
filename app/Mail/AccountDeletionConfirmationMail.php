<?php

namespace App\Mail;

use App\Models\User;
use App\Models\AccountDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public AccountDeletionRequest $deletionRequest;
    public string $cancelUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, AccountDeletionRequest $deletionRequest)
    {
        $this->user = $user;
        $this->deletionRequest = $deletionRequest;
        $this->cancelUrl = route('account.deletion.cancel', [
            'token' => $deletionRequest->confirmation_token
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Deletion Confirmed - 24 Hour Grace Period Active - HD Tickets',
            tags: ['account-deletion', 'confirmation'],
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
            html: 'emails.account-deletion-confirmation',
            text: 'emails.account-deletion-confirmation-text',
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
