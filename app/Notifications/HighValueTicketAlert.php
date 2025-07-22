<?php

namespace App\Notifications;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class HighValueTicketAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $alert;
    protected $matchScore;

    /**
     * Create a new notification instance.
     */
    public function __construct(ScrapedTicket $ticket, TicketAlert $alert, int $matchScore = 100)
    {
        $this->ticket = $ticket;
        $this->alert = $alert;
        $this->matchScore = $matchScore;
        
        // Set queue priority - high-value tickets get higher priority
        $this->onQueue($ticket->is_high_demand ? 'high-priority' : 'notifications');
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database']; // Always store in database for in-app notifications

        // Add email if user has email notifications enabled for this alert
        if ($this->alert->email_notifications && $notifiable->email) {
            $channels[] = 'mail';
        }

        // Add SMS if user has SMS notifications enabled
        if ($this->alert->sms_notifications && $notifiable->phone) {
            $channels[] = SmsChannel::class;
        }

        // Add broadcast for real-time in-app notifications
        if ($this->ticket->is_high_demand) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $urgencyLevel = $this->ticket->is_high_demand ? 'HIGH PRIORITY' : 'Alert';
        $demandBadge = $this->ticket->is_high_demand ? '🔥 HIGH DEMAND' : '';

        return (new MailMessage)
            ->subject("[$urgencyLevel] High-Value Ticket Alert: {$this->ticket->event_title}")
            ->greeting("Hello {$notifiable->username}!")
            ->line("🎟️ **Your ticket alert \"{$this->alert->name}\" has found a match!** {$demandBadge}")
            ->line("")
            ->line("**Event:** {$this->ticket->event_title}")
            ->line("**Platform:** {$this->ticket->platform_display_name}")
            ->line("**Venue:** {$this->ticket->venue}")
            ->line("**Date:** {$this->ticket->event_date->format('M j, Y \a\t g:i A')}")
            ->line("**Price:** {$this->ticket->formatted_price}")
            ->line("**Section:** {$this->ticket->section ?: 'Not specified'}")
            ->line("**Row:** {$this->ticket->row ?: 'Not specified'}")
            ->line("**Available Quantity:** {$this->ticket->quantity_available}")
            ->line("")
            ->when($this->ticket->is_high_demand, function($mail) {
                return $mail->line("⚡ **This is a HIGH DEMAND ticket!** (Demand Score: {$this->ticket->demand_score}/100)")
                           ->line("🚨 **Act fast - these tickets sell quickly!**");
            })
            ->line("**Match Score:** {$this->matchScore}%")
            ->action('View Ticket Details', $this->ticket->ticket_url)
            ->line("This alert matched because:")
            ->line("• Keywords: \"{$this->alert->keywords}\"")
            ->when($this->alert->max_price, function($mail) {
                return $mail->line("• Maximum price: {$this->alert->formatted_max_price}");
            })
            ->when($this->alert->platform, function($mail) {
                return $mail->line("• Platform: {$this->alert->platform_display_name}");
            })
            ->line("")
            ->line("To stop receiving alerts for this search, you can disable the alert in your dashboard.")
            ->salutation("Happy ticket hunting! 🎭");
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms($notifiable): string
    {
        $urgency = $this->ticket->is_high_demand ? '🔥 HIGH DEMAND' : '🎟️';
        
        return "{$urgency} Ticket Alert: {$this->ticket->event_title} at {$this->ticket->venue} on {$this->ticket->event_date->format('M j')} - {$this->ticket->formatted_price}. Section: {$this->ticket->section}. Available: {$this->ticket->quantity_available}. View: {$this->ticket->ticket_url}";
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'high_value_ticket_alert',
            'urgency' => $this->ticket->is_high_demand ? 'high' : 'normal',
            'ticket' => [
                'id' => $this->ticket->id,
                'uuid' => $this->ticket->uuid,
                'event_title' => $this->ticket->event_title,
                'platform' => $this->ticket->platform,
                'platform_display_name' => $this->ticket->platform_display_name,
                'venue' => $this->ticket->venue,
                'event_date' => $this->ticket->event_date->toISOString(),
                'price' => $this->ticket->total_price,
                'formatted_price' => $this->ticket->formatted_price,
                'currency' => $this->ticket->currency,
                'section' => $this->ticket->section,
                'row' => $this->ticket->row,
                'quantity_available' => $this->ticket->quantity_available,
                'is_high_demand' => $this->ticket->is_high_demand,
                'demand_score' => $this->ticket->demand_score,
                'ticket_url' => $this->ticket->ticket_url,
                'image_url' => $this->ticket->image_url
            ],
            'alert' => [
                'id' => $this->alert->id,
                'uuid' => $this->alert->uuid,
                'name' => $this->alert->name,
                'keywords' => $this->alert->keywords,
                'max_price' => $this->alert->max_price,
                'formatted_max_price' => $this->alert->formatted_max_price
            ],
            'match_score' => $this->matchScore,
            'timestamp' => now()->toISOString(),
            'message' => "High-value ticket alert: {$this->ticket->event_title}"
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'high_value_ticket_alert',
            'urgency' => $this->ticket->is_high_demand ? 'high' : 'normal',
            'ticket_id' => $this->ticket->id,
            'ticket_uuid' => $this->ticket->uuid,
            'alert_id' => $this->alert->id,
            'alert_uuid' => $this->alert->uuid,
            'alert_name' => $this->alert->name,
            'event_title' => $this->ticket->event_title,
            'platform' => $this->ticket->platform,
            'platform_display_name' => $this->ticket->platform_display_name,
            'venue' => $this->ticket->venue,
            'event_date' => $this->ticket->event_date->toISOString(),
            'formatted_event_date' => $this->ticket->event_date->format('M j, Y \a\t g:i A'),
            'price' => $this->ticket->total_price,
            'formatted_price' => $this->ticket->formatted_price,
            'currency' => $this->ticket->currency,
            'section' => $this->ticket->section,
            'row' => $this->ticket->row,
            'quantity_available' => $this->ticket->quantity_available,
            'is_high_demand' => $this->ticket->is_high_demand,
            'demand_score' => $this->ticket->demand_score,
            'ticket_url' => $this->ticket->ticket_url,
            'image_url' => $this->ticket->image_url,
            'match_score' => $this->matchScore,
            'keywords_matched' => $this->alert->keywords,
            'message' => "High-value ticket alert: {$this->ticket->event_title} at {$this->ticket->venue} - {$this->ticket->formatted_price}",
            'scraped_at' => $this->ticket->scraped_at->toISOString(),
            'is_recent' => $this->ticket->is_recent
        ];
    }

    /**
     * Get the notification's channels.
     */
    public function broadcastOn()
    {
        return ['ticket-alerts.' . $this->alert->user_id];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return $this->toArray($this->alert->user);
    }
}
