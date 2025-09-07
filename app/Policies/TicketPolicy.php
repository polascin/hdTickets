<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

use function in_array;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tickets.
     */
    /**
     * ViewAny
     */
    public function viewAny(User $user): bool
    {
        return TRUE; // All authenticated users can view tickets list
    }

    /**
     * Determine whether the user can view the ticket.
     */
    /**
     * View
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Admins can view all tickets
        if ($user->isAdmin()) {
            return TRUE;
        }

        // Agents can view all tickets
        if ($user->isAgent()) {
            return TRUE;
        }

        // Customers can only view their own tickets
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can create tickets.
     */
    /**
     * Create
     */
    public function create(User $user): bool
    {
        return TRUE; // All authenticated users can create tickets
    }

    /**
     * Determine whether the user can update the ticket.
     */
    /**
     * Update
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Admins can update all tickets
        if ($user->isAdmin()) {
            return TRUE;
        }

        // Agents can update all tickets
        if ($user->isAgent()) {
            return TRUE;
        }

        // Customers can update their own tickets if they're not closed
        if ($user->id === $ticket->user_id) {
            return !$ticket->isClosed();
        }

        return FALSE;
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    /**
     * Delete
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Only admins can delete tickets
        if ($user->isAdmin()) {
            return TRUE;
        }

        // Customers can delete their own tickets only if they're open and have no comments
        if ($user->id === $ticket->user_id && $ticket->status === Ticket::STATUS_OPEN) {
            return $ticket->comments()->count() === 0;
        }

        return FALSE;
    }

    /**
     * Determine whether the user can restore the ticket.
     */
    /**
     * Restore
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the ticket.
     */
    /**
     * ForceDelete
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    /**
     * Assign
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // Only admins and agents can assign tickets
        return $user->isAdmin() || $user->isAgent();
    }

    /**
     * Determine whether the user can change ticket status.
     */
    /**
     * UpdateStatus
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        // Admins and agents can update status
        if ($user->isAdmin() || $user->isAgent()) {
            return TRUE;
        }

        // Customers can close their own tickets
        if ($user->id === $ticket->user_id) {
            return in_array($ticket->status, [
                Ticket::STATUS_OPEN,
                Ticket::STATUS_RESOLVED,
            ], TRUE);
        }

        return FALSE;
    }

    /**
     * Determine whether the user can change ticket priority.
     */
    /**
     * UpdatePriority
     */
    public function updatePriority(User $user, Ticket $ticket): bool
    {
        // Only admins and agents can update priority
        return $user->isAdmin() || $user->isAgent();
    }

    /**
     * Determine whether the user can add comments to the ticket.
     */
    /**
     * AddComment
     */
    public function addComment(User $user, Ticket $ticket): bool
    {
        // All users who can view the ticket can comment
        return $this->view($user, $ticket);
    }

    /**
     * Determine whether the user can add internal notes.
     */
    /**
     * AddInternalNote
     */
    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        // Only admins and agents can add internal notes
        return $user->isAdmin() || $user->isAgent();
    }

    /**
     * Determine whether the user can view internal comments.
     */
    /**
     * ViewInternalComments
     */
    public function viewInternalComments(User $user): bool
    {
        // Only admins and agents can view internal comments
        return $user->isAdmin() || $user->isAgent();
    }

    /**
     * Determine whether the user can bulk update tickets.
     */
    /**
     * BulkUpdate
     */
    public function bulkUpdate(User $user): bool
    {
        // Only admins and agents can perform bulk updates
        return $user->isAdmin() || $user->isAgent();
    }
}
