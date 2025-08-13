<?php declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     */
    /**
     * Via
     *
     * @param mixed $notifiable
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     */
    /**
     * ToMail
     *
     * @param mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return new MailMessage()
            ->subject('New Ticket Created: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->username . '!')
            ->line('A new ticket has been created:')
            ->line('**Ticket #' . $this->ticket->id . ':** ' . $this->ticket->title)
            ->line('**Category:** ' . ($this->ticket->category->name ?? 'Not specified'))
            ->line('**Priority:** ' . ucfirst($this->ticket->priority))
            ->line('**Created by:** ' . ($this->ticket->user->username ?? 'System'))
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('You are receiving this notification because you are an administrator or agent.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    /**
     * ToArray
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'type'            => 'ticket_created',
            'ticket_id'       => $this->ticket->id,
            'ticket_uuid'     => $this->ticket->uuid,
            'ticket_title'    => $this->ticket->title,
            'ticket_priority' => $this->ticket->priority,
            'created_by'      => $this->ticket->user->username ?? 'System',
            'message'         => 'New ticket created: ' . $this->ticket->title,
        ];
    }
}
