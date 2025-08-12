<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\TicketAvailabilityUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Http\Requests\Api\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function in_array;
use function is_array;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query()
            ->with(['user', 'assignedTo', 'category', 'comments', 'attachments'])
            ->withCount(['comments', 'attachments']);

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('assigned_to')) {
            $query->byAssignee($request->assigned_to);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('source')) {
            $query->bySource($request->source);
        }

        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : [$request->tags];
            foreach ($tags as $tag) {
                $query->withTag($tag);
            }
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Filter by open/closed status
        if ($request->filled('is_open')) {
            if ($request->boolean('is_open')) {
                $query->open();
            } else {
                $query->closed();
            }
        }

        // Filter by high priority
        if ($request->boolean('high_priority')) {
            $query->highPriority();
        }

        // Filter by overdue
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['id', 'title', 'status', 'priority', 'created_at', 'updated_at', 'due_date', 'last_activity_at'];

        if (in_array($sortField, $allowedSorts, TRUE)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Apply pagination
        $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
        $tickets = $query->paginate($perPage);

        return response()->json([
            'data' => TicketResource::collection($tickets),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'from'         => $tickets->firstItem(),
                'last_page'    => $tickets->lastPage(),
                'per_page'     => $tickets->perPage(),
                'to'           => $tickets->lastItem(),
                'total'        => $tickets->total(),
            ],
            'links' => [
                'first' => $tickets->url(1),
                'last'  => $tickets->url($tickets->lastPage()),
                'prev'  => $tickets->previousPageUrl(),
                'next'  => $tickets->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $ticket = Ticket::create($data);

        // Load relationships for response
        $ticket->load(['user', 'assignedTo', 'category']);

        return response()->json(new TicketResource($ticket), 201);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        // Load all relationships for detailed view
        $ticket->load([
            'user',
            'assignedTo',
            'category',
            'comments' => function ($query): void {
                $query->with(['user', 'attachments'])->orderBy('created_at', 'asc');
            },
            'attachments.user',
        ]);

        return response()->json(new TicketResource($ticket));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $oldStatus = $ticket->status;

        $ticket->update($request->validated());

        // Create system comment for status changes
        if (isset($request->validated()['status']) && $oldStatus !== $ticket->status) {
            \App\Models\Comment::createStatusChangeComment(
                $ticket,
                $oldStatus,
                $ticket->status,
                auth()->user(),
            );
        }

        // Load relationships for response
        $ticket->load(['user', 'assignedTo', 'category']);

        return response()->json(new TicketResource($ticket));
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(NULL, 204);
    }

    /**
     * Broadcast ticket availability update.
     */
    public function availabilityUpdate(Request $request): JsonResponse
    {
        // Validate request
        $data = $request->validate([
            'ticket_uuid' => 'required|string|exists:tickets,uuid',
            'status'      => 'required|string',
        ]);

        // Broadcast the ticket update
        broadcast(new TicketAvailabilityUpdated($data['ticket_uuid'], $data['status']));

        return response()->json(['message' => 'Ticket availability update broadcasted successfully']);
    }
}
