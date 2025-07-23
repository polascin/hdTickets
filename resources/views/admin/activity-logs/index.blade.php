@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Activity Logs</h4>
                    <div>
                        <a href="{{ route('admin.activity-logs.export', request()->query()) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($stats['total_activities']) }}</h5>
                                    <small>Total Activities</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($stats['today']) }}</h5>
                                    <small>Today</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($stats['this_week']) }}</h5>
                                    <small>This Week</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($stats['security_events']) }}</h5>
                                    <small>Security Events</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($stats['high_risk_events']) }}</h5>
                                    <small>High Risk</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h5>{{ number_format($stats['bulk_operations']) }}</h5>
                                    <small>Bulk Ops</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label for="log_name" class="form-label">Log Type</label>
                            <select name="log_name" id="log_name" class="form-select form-select-sm">
                                <option value="all" {{ $logName === 'all' ? 'selected' : '' }}>All Types</option>
                                @foreach($logNames as $name)
                                    <option value="{{ $name }}" {{ $logName === $name ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="user_id" class="form-label">User</label>
                            <select name="user_id" id="user_id" class="form-select form-select-sm">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} {{ $user->surname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="risk_level" class="form-label">Risk Level</label>
                            <select name="risk_level" id="risk_level" class="form-select form-select-sm">
                                <option value="">All Levels</option>
                                <option value="low" {{ $riskLevel === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $riskLevel === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $riskLevel === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" 
                                   value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" 
                                   value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Activity Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Description</th>
                                    <th>Risk</th>
                                    <th>IP</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->id }}</td>
                                        <td>
                                            <small title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $activity->log_name === 'security' ? 'danger' : ($activity->log_name === 'user_actions' ? 'primary' : 'secondary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($activity->causer)
                                                <small>{{ $activity->causer->name }} {{ $activity->causer->surname }}</small>
                                            @else
                                                <small class="text-muted">System</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($activity->description, 50) }}</small>
                                        </td>
                                        <td>
                                            @if(isset($activity->properties['risk_level']))
                                                <span class="badge bg-{{ $activity->properties['risk_level'] === 'high' ? 'danger' : ($activity->properties['risk_level'] === 'medium' ? 'warning' : 'success') }}">
                                                    {{ ucfirst($activity->properties['risk_level']) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $activity->properties['ip_address'] ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.activity-logs.show', $activity) }}" 
                                               class="btn btn-outline-primary btn-xs">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No activity logs found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} 
                                of {{ $activities->total() }} results
                            </small>
                        </div>
                        <div>
                            {{ $activities->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
@if(auth()->user()->isRootAdmin())
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clean Up Old Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cleanupForm">
                    @csrf
                    <div class="mb-3">
                        <label for="older_than_days" class="form-label">Delete logs older than (days):</label>
                        <input type="number" name="older_than_days" id="older_than_days" 
                               class="form-control" min="30" max="365" value="90" required>
                        <div class="form-text">Minimum 30 days, maximum 365 days</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="performCleanup()">Delete Old Logs</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function performCleanup() {
    const days = document.getElementById('older_than_days').value;
    
    if (confirm(`Are you sure you want to delete all activity logs older than ${days} days? This action cannot be undone.`)) {
        fetch('{{ route("admin.activity-logs.cleanup") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                older_than_days: parseInt(days)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cleaning up logs.');
        });
    }
}
</script>
@endpush
