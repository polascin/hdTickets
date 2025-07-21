<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusChanged;
use App\Notifications\TicketAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'assignedTo', 'category'])
            ->orderBy('last_activity_at', 'desc');

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->byPriority($request->priority);
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        if ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->byAssignee($request->assigned_to);
            }
        }

        if ($request->filled('user') && $request->user !== 'all') {
            $query->byUser($request->user);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'last_activity_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['created_at', 'updated_at', 'last_activity_at', 'due_date', 'priority', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Filter by user role
        $user = Auth::user();
        if ($user->isCustomer()) {
            $query->byUser($user->id);
        } elseif ($user->isAgent() && !$request->filled('show_all')) {
            // Agents see tickets assigned to them or unassigned tickets by default
            $query->where(function ($q) use ($user) {
                $q->byAssignee($user->id)->orWhereNull('assigned_to');
            });
        }

        $tickets = $query->paginate(15);

        // Get filter options
        $categories = Category::active()->orderBy('name')->get();
        $agents = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])
                     ->orderBy('username')->get();
        $customers = User::where('role', User::ROLE_CUSTOMER)
                        ->orderBy('username')->get();

        return view('tickets.index', compact(
            'tickets', 
            'categories', 
            'agents', 
            'customers'
        ));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        
        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = new Ticket($request->validated());
        $ticket->user_id = Auth::id();
        $ticket->status = Ticket::STATUS_OPEN;
        
        // Auto-assign priority if not provided
        if (!$ticket->priority) {
            $ticket->priority = Ticket::PRIORITY_MEDIUM;
        }
        
        // Set source
        $ticket->source = Ticket::SOURCE_WEB;
        
        $ticket->save();

        // Create initial comment if description is provided
        if ($request->filled('description')) {
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'content' => $request->description,
                'type' => Comment::TYPE_COMMENT,
                'is_internal' => false,
            ]);
        }

        // Send notification to admins and agents
        $adminsAndAgents = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])->get();
        Notification::send($adminsAndAgents, new TicketCreated($ticket));

        return redirect()->route('tickets.show', $ticket)
                        ->with('success', 'Ticket created successfully!');
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'user', 
            'assignedTo', 
            'category',
            'comments' => function($query) {
                $query->with(['user', 'attachments']);
            },
            'attachments'
        ]);

        // Get agents for assignment
        $agents = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_AGENT])
                     ->orderBy('username')->get();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    /**
     * Show the form for editing the ticket
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $categories = Category::active()->orderBy('name')->get();
        
        return view('tickets.edit', compact('ticket', 'categories'));
    }

    /**
     * Update the specified ticket
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $oldStatus = $ticket->status;
        $oldAssignee = $ticket->assigned_to;

        $ticket->update($request->validated());

        // Track status changes
        if ($oldStatus !== $ticket->status) {
            Comment::createStatusChangeComment($ticket, $oldStatus, $ticket->status, Auth::user());
            
            // Send notification
            $notifiableUsers = collect([$ticket->user, $ticket->assignedTo])
                ->filter()
                ->unique('id');
            
            if ($notifiableUsers->isNotEmpty()) {
                Notification::send($notifiableUsers, new TicketStatusChanged($ticket, $oldStatus));
            }
        }

        // Track assignment changes
        if ($oldAssignee !== $ticket->assigned_to) {
            Comment::createAssignmentComment($ticket, $ticket->assignedTo, Auth::user());
            
            if ($ticket->assignedTo) {
                $ticket->assignedTo->notify(new TicketAssigned($ticket));
            }
        }

        return redirect()->route('tickets.show', $ticket)
                        ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')
                        ->with('success', 'Ticket deleted successfully!');
    }

    /**
     * Update ticket status via AJAX
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'status' => 'required|in:' . implode(',', Ticket::getStatuses())
        ]);

        $oldStatus = $ticket->status;
        
        $ticket->update(['status' => $request->status]);

        // Create status change comment
        Comment::createStatusChangeComment($ticket, $oldStatus, $ticket->status, Auth::user());

        // Send notification
        $notifiableUsers = collect([$ticket->user, $ticket->assignedTo])
            ->filter()
            ->unique('id')
            ->reject(fn($user) => $user->id === Auth::id()); // Don't notify the person making the change
        
        if ($notifiableUsers->isNotEmpty()) {
            Notification::send($notifiableUsers, new TicketStatusChanged($ticket, $oldStatus));
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'status' => $ticket->status,
            'status_color' => $ticket->status_color
        ]);
    }

    /**
     * Update ticket priority via AJAX
     */
    public function updatePriority(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'priority' => 'required|in:' . implode(',', Ticket::getPriorities())
        ]);

        $ticket->update(['priority' => $request->priority]);

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully',
            'priority' => $ticket->priority,
            'priority_color' => $ticket->priority_color
        ]);
    }

    /**
     * Assign ticket to user via AJAX
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $oldAssignee = $ticket->assigned_to;
        
        $ticket->update(['assigned_to' => $request->assigned_to]);

        // Create assignment comment
        Comment::createAssignmentComment($ticket, $ticket->assignedTo, Auth::user());

        // Send notification to new assignee
        if ($ticket->assignedTo && $ticket->assignedTo->id !== Auth::id()) {
            $ticket->assignedTo->notify(new TicketAssigned($ticket));
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'assigned_to' => $ticket->assignedTo ? [
                'id' => $ticket->assignedTo->id,
                'name' => $ticket->assignedTo->username
            ] : null
        ]);
    }

    /**
     * Add comment to ticket
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $request->validate([
            'content' => 'required|string|max:5000',
            'is_internal' => 'boolean',
            'type' => 'in:' . implode(',', Comment::getTypes())
        ]);

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->get('type', Comment::TYPE_COMMENT),
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        $comment->load('user');

        // Send notification to relevant users
        $notifiableUsers = collect([$ticket->user, $ticket->assignedTo])
            ->filter()
            ->unique('id')
            ->reject(fn($user) => $user->id === Auth::id());
        
        // Don't send notifications for internal comments to customers
        if ($comment->is_internal) {
            $notifiableUsers = $notifiableUsers->reject(fn($user) => $user->isCustomer());
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user->username,
                    'created_at' => $comment->formatted_created_at,
                    'is_internal' => $comment->is_internal,
                    'type_color' => $comment->type_color
                ]
            ]);
        }

        return redirect()->route('tickets.show', $ticket)
                        ->with('success', 'Comment added successfully!');
    }
}
