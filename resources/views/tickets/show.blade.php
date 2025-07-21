@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Ticket Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $ticket->title }}</h1>
            <p class="text-muted">Ticket #{{ $ticket->id }} • Created {{ $ticket->created_at->diffForHumans() }}</p>
        </div>
        <div class="col-md-4 text-end">
            @can('update', $ticket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-secondary">Edit Ticket</a>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Ticket Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Ticket Details</h5>
                    <div>
                        <span class="badge bg-{{ $ticket->status_color }} me-2">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span class="badge bg-{{ $ticket->priority_color }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Created by:</strong> {{ $ticket->user->username ?? 'N/A' }}</p>
                            <p><strong>Category:</strong> {{ $ticket->category->name ?? 'N/A' }}</p>
                            <p><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Assigned to:</strong> {{ $ticket->assignedTo->username ?? 'Unassigned' }}</p>
                            <p><strong>Due date:</strong> {{ $ticket->due_date ? $ticket->due_date->format('M d, Y') : 'Not set' }}</p>
                            <p><strong>Last activity:</strong> {{ $ticket->last_activity_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    
                    @if($ticket->tags && count($ticket->tags) > 0)
                    <div class="mt-3">
                        <strong>Tags:</strong>
                        @foreach($ticket->tags as $tag)
                            <span class="badge bg-light text-dark me-1">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">
                    <h5>Comments ({{ $ticket->comments->count() }})</h5>
                </div>
                <div class="card-body" id="comments-section">
                    @if($ticket->comments->count() > 0)
                        @foreach($ticket->comments as $comment)
                            @if(!$comment->is_internal || auth()->user()->can('viewInternalComments'))
                            <div class="comment mb-4 @if($comment->is_internal) border-start border-warning border-3 ps-3 @endif" 
                                 id="comment-{{ $comment->id }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center">
                                        <strong>{{ $comment->user_display_name }}</strong>
                                        <span class="badge bg-{{ $comment->type_color }} ms-2 small">
                                            {{ ucfirst($comment->type) }}
                                        </span>
                                        @if($comment->is_internal)
                                            <span class="badge bg-warning ms-1 small">Internal</span>
                                        @endif
                                        @if($comment->is_solution)
                                            <span class="badge bg-success ms-1 small">Solution</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $comment->formatted_created_at }}
                                        @if($comment->is_edited)
                                            <span title="Last edited {{ $comment->edited_at->format('M d, Y g:i A') }}">
                                                (edited)
                                            </span>
                                        @endif
                                    </small>
                                </div>
                                <div class="comment-content">
                                    {!! nl2br(e($comment->content)) !!}
                                </div>
                                
                                @if($comment->attachments->count() > 0)
                                <div class="mt-2">
                                    <small class="text-muted">Attachments:</small>
                                    @foreach($comment->attachments as $attachment)
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                           class="btn btn-sm btn-outline-secondary ms-1"
                                           target="_blank">
                                            {{ $attachment->original_name }}
                                        </a>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>
                    @endif
                </div>
            </div>

            <!-- Add Comment Form -->
            @can('addComment', $ticket)
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Add Comment</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" id="comment-form">
                        @csrf
                        <div class="mb-3">
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      name="content" rows="4" 
                                      placeholder="Enter your comment..."
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @can('addInternalNote', $ticket)
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_internal" id="is_internal" 
                                   value="1" {{ old('is_internal') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_internal">
                                Internal note (not visible to customer)
                            </label>
                        </div>
                        @endcan
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary">Add Comment</button>
                            <small class="text-muted">All participants will be notified</small>
                        </div>
                    </form>
                </div>
            </div>
            @endcan
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            @can('update', $ticket)
            <div class="card mb-3">
                <div class="card-header">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <!-- Status Change -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="status-select" data-ticket-id="{{ $ticket->id }}">
                            @foreach(\App\Models\Ticket::getStatuses() as $status)
                                <option value="{{ $status }}" @if($ticket->status === $status) selected @endif>
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority Change -->
                    @can('updatePriority', $ticket)
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" id="priority-select" data-ticket-id="{{ $ticket->id }}">
                            @foreach(\App\Models\Ticket::getPriorities() as $priority)
                                <option value="{{ $priority }}" @if($ticket->priority === $priority) selected @endif>
                                    {{ ucfirst($priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endcan

                    <!-- Assignment -->
                    @can('assign', $ticket)
                    <div class="mb-3">
                        <label class="form-label">Assign to</label>
                        <select class="form-select" id="assign-select" data-ticket-id="{{ $ticket->id }}">
                            <option value="">Unassigned</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" @if($ticket->assigned_to === $agent->id) selected @endif>
                                    {{ $agent->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endcan
                </div>
            </div>
            @endcan

            <!-- Ticket Information -->
            <div class="card">
                <div class="card-header">
                    <h6>Ticket Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Ticket ID</small><br>
                        <span class="fw-bold">#{{ $ticket->id }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">UUID</small><br>
                        <code>{{ $ticket->uuid }}</code>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Source</small><br>
                        <span class="badge bg-secondary">{{ ucfirst($ticket->source) }}</span>
                    </div>
                    @if($ticket->first_response_at)
                    <div class="mb-2">
                        <small class="text-muted">First Response</small><br>
                        {{ $ticket->first_response_at->diffForHumans() }}
                    </div>
                    @endif
                    @if($ticket->resolved_at)
                    <div class="mb-2">
                        <small class="text-muted">Resolved</small><br>
                        {{ $ticket->resolved_at->diffForHumans() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status change
    const statusSelect = document.getElementById('status-select');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            updateTicketField('status', this.value, this.dataset.ticketId);
        });
    }

    // Priority change
    const prioritySelect = document.getElementById('priority-select');
    if (prioritySelect) {
        prioritySelect.addEventListener('change', function() {
            updateTicketField('priority', this.value, this.dataset.ticketId);
        });
    }

    // Assignment change
    const assignSelect = document.getElementById('assign-select');
    if (assignSelect) {
        assignSelect.addEventListener('change', function() {
            updateTicketField('assign', this.value, this.dataset.ticketId);
        });
    }

    function updateTicketField(field, value, ticketId) {
        const url = `/tickets/${ticketId}/${field}`;
        const data = {};
        
        if (field === 'assign') {
            data.assigned_to = value;
        } else {
            data[field] = value;
        }

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert(data.message, 'success');
                
                // Refresh the page to show updated status/comments
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('Error updating ticket', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error updating ticket', 'error');
        });
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
});
</script>
@endpush
@endsection
