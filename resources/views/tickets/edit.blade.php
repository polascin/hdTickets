@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Ticket #{{ $ticket->id }}</h1>
                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Ticket
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $ticket->title) }}" 
                                       required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                @if(old('category_id', $ticket->category_id) == $category->id) selected @endif>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    @foreach(\App\Models\Ticket::getPriorities() as $priority)
                                        <option value="{{ $priority }}" 
                                                @if(old('priority', $ticket->priority) === $priority) selected @endif>
                                            {{ ucfirst($priority) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    @foreach(\App\Models\Ticket::getStatuses() as $status)
                                        <option value="{{ $status }}" 
                                                @if(old('status', $ticket->status) === $status) selected @endif>
                                            {{ ucwords(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Assigned To</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                        id="assigned_to" 
                                        name="assigned_to">
                                    <option value="">Unassigned</option>
                                    @php
                                        $agents = \App\Models\User::whereIn('role', [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_AGENT])
                                                                  ->orderBy('username')->get();
                                    @endphp
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" 
                                                @if(old('assigned_to', $ticket->assigned_to) == $agent->id) selected @endif>
                                            {{ $agent->username }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @else
                        <!-- Hidden fields for customers -->
                        <input type="hidden" name="status" value="{{ $ticket->status }}">
                        <input type="hidden" name="assigned_to" value="{{ $ticket->assigned_to }}">
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" 
                                       class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" 
                                       name="due_date" 
                                       value="{{ old('due_date', $ticket->due_date ? $ticket->due_date->format('Y-m-d') : '') }}">
                                @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" 
                                   class="form-control @error('tags') is-invalid @enderror" 
                                   id="tags" 
                                   name="tags" 
                                   value="{{ old('tags', $ticket->tags ? implode(', ', $ticket->tags) : '') }}"
                                   placeholder="Enter tags separated by commas">
                            <div class="form-text">Separate multiple tags with commas</div>
                            @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-primary">Update Ticket</button>
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                            
                            @can('delete', $ticket)
                            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" 
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this ticket? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Delete Ticket</button>
                            </form>
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Current Ticket Information -->
            <div class="card">
                <div class="card-header">
                    <h6>Current Ticket Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Current Status</small><br>
                        <span class="badge bg-{{ $ticket->status_color }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Current Priority</small><br>
                        <span class="badge bg-{{ $ticket->priority_color }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Created</small><br>
                        {{ $ticket->created_at->format('M d, Y g:i A') }}
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Last Updated</small><br>
                        {{ $ticket->updated_at->diffForHumans() }}
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Last Activity</small><br>
                        {{ $ticket->last_activity_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2">• Changes to status and assignment will create activity log entries</li>
                        <li class="mb-2">• Relevant users will be notified of changes via email</li>
                        <li class="mb-2">• Use tags to help categorize and find tickets later</li>
                        @if($ticket->comments->count() > 0)
                        <li class="mb-2">• This ticket has {{ $ticket->comments->count() }} comment(s)</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
