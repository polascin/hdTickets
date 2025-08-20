<?php declare(strict_types=1);

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    protected $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $oldStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
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
        $statusFrom = ucwords(str_replace('_', ' ', $this->oldStatus));
        $statusTo = ucwords(str_replace('_', ' ', $this->ticket->status));

        return (new MailMessage)
            ->subject('Ticket Status Updated: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->username . '!')
            ->line('The status of ticket #' . $this->ticket->id . ' has been updated:')
            ->line('**Ticket:** ' . $this->ticket->title)
            ->line('**Status changed from:** ' . $statusFrom)
            ->line('**Status changed to:** ' . $statusTo)
            ->line('**Updated by:** ' . (auth()->user()->username ?? 'System'))
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Thank you for using our ticketing system!');
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
            'type'         => 'ticket_status_changed',
            'ticket_id'    => $this->ticket->id,
            'ticket_uuid'  => $this->ticket->uuid,
            'ticket_title' => $this->ticket->title,
            'old_status'   => $this->oldStatus,
            'new_status'   => $this->ticket->status,
            'updated_by'   => auth()->user()->username ?? 'System',
            'message'      => 'Ticket status changed from ' . ucwords(str_replace('_', ' ', $this->oldStatus)) . ' to ' . ucwords(str_replace('_', ' ', $this->ticket->status)),
        ];
    }
}