<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Campaign Email Mailable
 *
 * Handles sending marketing campaign emails with:
 * - Personalized content and tracking
 * - Professional HTML templates
 * - Click and open tracking
 * - Unsubscribe functionality
 */
class CampaignEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public MarketingCampaign $campaign,
        public User $user,
        public array $content,
    ) {
    }

    /**
     * Get the message envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->content['subject'],
            from: config('mail.from.address', 'noreply@hdtickets.com'),
            replyTo: config('mail.reply_to.address', 'support@hdtickets.com'),
        );
    }

    /**
     * Get the message content definition
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.campaign.template',
            text: 'emails.campaign.template-text',
            with: [
                'campaign'         => $this->campaign,
                'user'             => $this->user,
                'content'          => $this->content,
                'trackingPixelUrl' => $this->generateTrackingPixelUrl(),
                'unsubscribeUrl'   => $this->generateUnsubscribeUrl(),
                'clickTrackingUrl' => $this->generateClickTrackingUrl(),
            ],
        );
    }

    /**
     * Generate tracking pixel URL for open tracking
     */
    private function generateTrackingPixelUrl(): string
    {
        return route('campaign.track.open', [
            'campaign' => $this->campaign->id,
            'user'     => $this->user->id,
        ]);
    }

    /**
     * Generate unsubscribe URL
     */
    private function generateUnsubscribeUrl(): string
    {
        return url('/unsubscribe/' . base64_encode($this->user->email . '|' . $this->campaign->id));
    }

    /**
     * Generate click tracking URL
     */
    private function generateClickTrackingUrl(): string
    {
        return route('campaign.track.click', [
            'campaign' => $this->campaign->id,
            'user'     => $this->user->id,
        ]);
    }
}
