<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketManagementController extends Controller
{
    /**
     * Display a listing of tickets for management
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'assignedTo'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by assigned agent
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->byAssignee($request->assigned_to);
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $tickets = $query->paginate(25);

        // Get filter options
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        $categories = Category::active()->ordered()->get();
        $statuses = Ticket::getStatuses();
        $priorities = Ticket::getPriorities();

        // Get statistics for the dashboard cards
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ];

        return view('admin.tickets.index', compact(
            'tickets', 
            'agents', 
            'categories', 
            'statuses', 
            'priorities',
            'stats'
        ));
    }

    /**
     * Assign ticket to agent
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        // Verify the user is an agent or admin
        if ($request->assigned_to) {
            $assignee = User::findOrFail($request->assigned_to);
            if (!$assignee->isAgent() && !$assignee->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be an agent or admin to be assigned tickets.'
                ], 422);
            }
        }

        $oldAssignee = $ticket->assignedTo;
        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => $ticket->status === Ticket::STATUS_OPEN ? Ticket::STATUS_IN_PROGRESS : $ticket->status
        ]);

        // Log the assignment change
        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => $this->getAssignmentMessage($oldAssignee, $ticket->assignedTo),
            'is_internal' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket assigned successfully.',
                'assigned_to' => $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned'
            ]);
        }

        return redirect()->back()->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Bulk assign tickets
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'ticket_ids' => ['required', 'array'],
            'ticket_ids.*' => ['exists:tickets,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        // Verify the user is an agent or admin if assigning
        if ($request->assigned_to) {
            $assignee = User::findOrFail($request->assigned_to);
            if (!$assignee->isAgent() && !$assignee->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be an agent or admin to be assigned tickets.'
                ], 422);
            }
        }

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();
        $assignedCount = 0;

        DB::transaction(function () use ($tickets, $request, &$assignedCount) {
            foreach ($tickets as $ticket) {
                $oldAssignee = $ticket->assignedTo;
                
                $ticket->update([
                    'assigned_to' => $request->assigned_to,
                    'status' => $ticket->status === Ticket::STATUS_OPEN ? Ticket::STATUS_IN_PROGRESS : $ticket->status
                ]);

                // Log the assignment change
                $ticket->comments()->create([
                    'user_id' => auth()->id(),
                    'content' => $this->getAssignmentMessage($oldAssignee, $ticket->assignedTo) . ' (Bulk Assignment)',
                    'is_internal' => true,
                ]);

                $assignedCount++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Successfully assigned {$assignedCount} tickets."
        ]);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => ['required', Rule::in(Ticket::getStatuses())],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $ticket->status;
        
        // If resolving, set resolved_at timestamp
        if ($request->status === Ticket::STATUS_RESOLVED && $oldStatus !== Ticket::STATUS_RESOLVED) {
            $ticket->resolved_at = now();
        }

        // If reopening a resolved ticket, clear resolved_at
        if ($oldStatus === Ticket::STATUS_RESOLVED && $request->status !== Ticket::STATUS_RESOLVED) {
            $ticket->resolved_at = null;
        }

        $ticket->update(['status' => $request->status]);

        // Add comment if provided
        if ($request->filled('comment')) {
            $ticket->comments()->create([
                'user_id' => auth()->id(),
                'content' => $request->comment,
                'is_internal' => false,
            ]);
        }

        // Log status change
        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => "Status changed from " . ucfirst(str_replace('_', ' ', $oldStatus)) . " to " . ucfirst(str_replace('_', ' ', $request->status)),
            'is_internal' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket status updated successfully.',
                'status' => $ticket->status
            ]);
        }

        return redirect()->back()->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $request->validate([
            'priority' => ['required', Rule::in(Ticket::getPriorities())],
        ]);

        $oldPriority = $ticket->priority;
        $ticket->update(['priority' => $request->priority]);

        // Log priority change
        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => "Priority changed from " . ucfirst($oldPriority) . " to " . ucfirst($request->priority),
            'is_internal' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket priority updated successfully.',
                'priority' => $ticket->priority
            ]);
        }

        return redirect()->back()->with('success', 'Ticket priority updated successfully.');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ticket_ids' => ['required', 'array'],
            'ticket_ids.*' => ['exists:tickets,id'],
            'status' => ['required', Rule::in(Ticket::getStatuses())],
        ]);

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();
        $updatedCount = 0;

        DB::transaction(function () use ($tickets, $request, &$updatedCount) {
            foreach ($tickets as $ticket) {
                $oldStatus = $ticket->status;
                
                // Skip if status is the same
                if ($oldStatus === $request->status) {
                    continue;
                }

                // If resolving, set resolved_at timestamp
                if ($request->status === Ticket::STATUS_RESOLVED && $oldStatus !== Ticket::STATUS_RESOLVED) {
                    $ticket->resolved_at = now();
                }

                // If reopening a resolved ticket, clear resolved_at
                if ($oldStatus === Ticket::STATUS_RESOLVED && $request->status !== Ticket::STATUS_RESOLVED) {
                    $ticket->resolved_at = null;
                }

                $ticket->update(['status' => $request->status]);

                // Log status change
                $ticket->comments()->create([
                    'user_id' => auth()->id(),
                    'content' => "Status changed from " . ucfirst(str_replace('_', ' ', $oldStatus)) . " to " . ucfirst(str_replace('_', ' ', $request->status)) . " (Bulk Update)",
                    'is_internal' => true,
                ]);

                $updatedCount++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Successfully updated status for {$updatedCount} tickets."
        ]);
    }

    /**
     * Set due date for ticket
     */
    public function setDueDate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'due_date' => ['nullable', 'date', 'after:today'],
        ]);

        $oldDueDate = $ticket->due_date;
        $ticket->update(['due_date' => $request->due_date]);

        // Log due date change
        $message = $request->due_date 
            ? "Due date set to " . $request->due_date
            : "Due date removed";

        if ($oldDueDate) {
            $message = $request->due_date 
                ? "Due date changed from {$oldDueDate->format('M j, Y')} to {$ticket->due_date->format('M j, Y')}"
                : "Due date removed (was {$oldDueDate->format('M j, Y')})";
        }

        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => $message,
            'is_internal' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Due date updated successfully.',
                'due_date' => $ticket->due_date ? $ticket->due_date->format('M j, Y') : null
            ]);
        }

        return redirect()->back()->with('success', 'Due date updated successfully.');
    }

    /**
     * Get assignment message for comments
     */
    private function getAssignmentMessage($oldAssignee, $newAssignee)
    {
        if (!$oldAssignee && !$newAssignee) {
            return "Ticket assignment unchanged";
        }

        if (!$oldAssignee && $newAssignee) {
            return "Ticket assigned to {$newAssignee->name}";
        }

        if ($oldAssignee && !$newAssignee) {
            return "Ticket unassigned from {$oldAssignee->name}";
        }

        return "Ticket reassigned from {$oldAssignee->name} to {$newAssignee->name}";
    }

    /**
     * Get ticket statistics for dashboard widgets
     */
    public function getStatistics()
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::open()->count(),
            'in_progress' => Ticket::byStatus(Ticket::STATUS_IN_PROGRESS)->count(),
            'pending' => Ticket::byStatus(Ticket::STATUS_PENDING)->count(),
            'resolved' => Ticket::byStatus(Ticket::STATUS_RESOLVED)->count(),
            'overdue' => Ticket::overdue()->count(),
            'high_priority' => Ticket::highPriority()->count(),
            'unassigned' => Ticket::whereNull('assigned_to')->open()->count(),
        ];

        return response()->json($stats);
    }
}
