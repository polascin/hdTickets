<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a ticket.
     */
    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        $query = $ticket->comments()
            ->with(['user', 'attachments'])
            ->withCount(['attachments']);

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('is_internal')) {
            if ($request->boolean('is_internal')) {
                $query->internal();
            } else {
                $query->public();
            }
        }

        if ($request->filled('is_solution')) {
            if ($request->boolean('is_solution')) {
                $query->solutions();
            }
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['id', 'created_at', 'updated_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'asc');
        }

        // Apply pagination
        $perPage = min($request->get('per_page', 20), 100);
        $comments = $query->paginate($perPage);

        return response()->json([
            'data' => CommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'from' => $comments->firstItem(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'to' => $comments->lastItem(),
                'total' => $comments->total(),
            ],
            'links' => [
                'first' => $comments->url(1),
                'last' => $comments->url($comments->lastPage()),
                'prev' => $comments->previousPageUrl(),
                'next' => $comments->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(StoreCommentRequest $request, Ticket $ticket): JsonResponse
    {
        $comment = $ticket->comments()->create(array_merge(
            $request->validated(),
            ['user_id' => auth()->id()]
        ));

        // Load relationships for response
        $comment->load(['user', 'attachments']);

        return response()->json(new CommentResource($comment), 201);
    }

    /**
     * Display the specified comment.
     */
    public function show(Comment $comment): JsonResponse
    {
        $comment->load(['user', 'ticket', 'attachments']);
        return response()->json(new CommentResource($comment));
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Only allow the comment author or admin/agent to update
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isAgent()) {
            return response()->json(['message' => 'Unauthorized to update this comment'], 403);
        }

        $request->validate([
            'content' => 'sometimes|required|string',
            'is_internal' => 'sometimes|boolean',
            'is_solution' => 'sometimes|boolean',
        ]);

        // Handle solution marking
        if ($request->has('is_solution') && $request->boolean('is_solution')) {
            $comment->markAsSolution();
        } elseif ($request->has('is_solution') && !$request->boolean('is_solution')) {
            $comment->unmarkAsSolution();
        }

        $comment->update($request->only(['content', 'is_internal']));
        $comment->load(['user', 'attachments']);

        return response()->json(new CommentResource($comment));
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Only allow the comment author or admin to delete
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized to delete this comment'], 403);
        }

        $comment->delete();
        return response()->json(null, 204);
    }
}
