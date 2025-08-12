<?php declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification implements ShouldQueue
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
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return new MailMessage()
            ->subject('Ticket Assigned to You: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->username . '!')
            ->line('A ticket has been assigned to you:')
            ->line('**Ticket #' . $this->ticket->id . ':** ' . $this->ticket->title)
            ->line('**Category:** ' . ($this->ticket->category->name ?? 'Not specified'))
            ->line('**Priority:** ' . ucfirst($this->ticket->priority))
            ->line('**Status:** ' . ucwords(str_replace('_', ' ', $this->ticket->status)))
            ->line('**Created by:** ' . ($this->ticket->user->username ?? 'System'))
            ->line('**Assigned by:** ' . (auth()->user()->username ?? 'System'))
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Please review the ticket and take appropriate action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'type'            => 'ticket_assigned',
            'ticket_id'       => $this->ticket->id,
            'ticket_uuid'     => $this->ticket->uuid,
            'ticket_title'    => $this->ticket->title,
            'ticket_priority' => $this->ticket->priority,
            'ticket_status'   => $this->ticket->status,
            'assigned_by'     => auth()->user()->username ?? 'System',
            'message'         => 'Ticket assigned to you: ' . $this->ticket->title,
        ];
    }
}
