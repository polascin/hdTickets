@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tickets</h1>
        @can('create', \App\Models\Ticket::class)
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">Create New Ticket</a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('tickets.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="all">All Statuses</option>
                            @foreach(\App\Models\Ticket::getStatuses() as $status)
                                <option value="{{ $status }}" @if(request('status') === $status) selected @endif>
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            <option value="all">All Priorities</option>
                            @foreach(\App\Models\Ticket::getPriorities() as $priority)
                                <option value="{{ $priority }}" @if(request('priority') === $priority) selected @endif>
                                    {{ ucfirst($priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="all">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @if(request('category') == $category->id) selected @endif>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                    <div class="col-md-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select class="form-select" name="assigned_to">
                            <option value="all">All Assignees</option>
                            <option value="unassigned" @if(request('assigned_to') === 'unassigned') selected @endif>Unassigned</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" @if(request('assigned_to') == $agent->id) selected @endif>
                                    {{ $agent->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search tickets...">
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select class="form-select" name="sort_by">
                            <option value="last_activity_at" @if(request('sort_by') === 'last_activity_at') selected @endif>Last Activity</option>
                            <option value="created_at" @if(request('sort_by') === 'created_at') selected @endif>Created Date</option>
                            <option value="priority" @if(request('sort_by') === 'priority') selected @endif>Priority</option>
                            <option value="status" @if(request('sort_by') === 'status') selected @endif>Status</option>
                            <option value="due_date" @if(request('sort_by') === 'due_date') selected @endif>Due Date</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <select class="form-select" name="sort_order">
                            <option value="desc" @if(request('sort_order', 'desc') === 'desc') selected @endif>Descending</option>
                            <option value="asc" @if(request('sort_order') === 'asc') selected @endif>Ascending</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <button type="submit" class="btn btn-secondary">Apply Filters</button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body">
            @if($tickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Category</th>
                                <th>Created By</th>
                                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                                <th>Assigned To</th>
                                @endif
                                <th>Created</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                        #{{ $ticket->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                        {{ Str::limit($ticket->title, 50) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $ticket->status_color }}">
                                        {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $ticket->priority_color }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                <td>{{ $ticket->user->username ?? 'N/A' }}</td>
                                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                                <td>{{ $ticket->assignedTo->username ?? 'Unassigned' }}</td>
                                @endif
                                <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                <td>{{ $ticket->last_activity_at->diffForHumans() }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-primary">View</a>
                                        @can('update', $ticket)
                                        <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-outline-secondary">Edit</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} results
                    </div>
                    <div>
                        {{ $tickets->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">No tickets found matching your criteria.</p>
                    @can('create', \App\Models\Ticket::class)
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary">Create Your First Ticket</a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
