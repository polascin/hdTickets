@extends('layouts.modern')
@section('title', 'Response Time Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Response Time</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Response Time Report</h1>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'response_time', 'format' => 'csv']) }}" class="btn btn-outline-primary">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Response Time Statistics -->
    <div class="row mb-4">
        @if(isset($stats))
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Average Response Time
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['avg_response_time'] ? round($stats['avg_response_time']) . ' min' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Median Response Time
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['median_response_time'] ? round($stats['median_response_time']) . ' min' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Fastest Response
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['fastest_response'] ? $stats['fastest_response'] . ' min' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Slowest Response
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $stats['slowest_response'] ? number_format($stats['slowest_response']) . ' min' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Response Time Distribution -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Response Time Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="responseTimeChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">SLA Performance</h6>
                </div>
                <div class="card-body">
                    @if(isset($stats))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm">Within 1 Hour</span>
                            <span class="text-sm">{{ $stats['within_1_hour'] }} tickets</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: {{ $responseTimeData->count() > 0 ? ($stats['within_1_hour'] / $responseTimeData->count()) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm">Within 4 Hours</span>
                            <span class="text-sm">{{ $stats['within_4_hours'] }} tickets</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-warning" style="width: {{ $responseTimeData->count() > 0 ? ($stats['within_4_hours'] / $responseTimeData->count()) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm">Within 24 Hours</span>
                            <span class="text-sm">{{ $stats['within_24_hours'] }} tickets</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" style="width: {{ $responseTimeData->count() > 0 ? ($stats['within_24_hours'] / $responseTimeData->count()) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Response Time Data -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Response Time Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Priority</th>
                            <th>Category</th>
                            <th>Customer</th>
                            <th>Assigned To</th>
                            <th>Created At</th>
                            <th>First Response</th>
                            <th>Response Time</th>
                            <th>SLA Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($responseTimeData as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="text-decoration-none">
                                    <strong>{{ $ticket->title }}</strong>
                                    <br>
                                    <small class="text-muted">#{{ $ticket->id }}</small>
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ $ticket->priority === 'urgent' ? 'bg-danger' : ($ticket->priority === 'high' ? 'bg-warning' : 'bg-info') }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                            <td>{{ $ticket->user->full_name ?? 'N/A' }}</td>
                            <td>{{ $ticket->assignedTo->full_name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="text-muted">{{ $ticket->created_at->format('M j, Y g:i A') }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $ticket->first_response_at ? $ticket->first_response_at->format('M j, Y g:i A') : 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $ticket->response_minutes <= 60 ? 'bg-success' : ($ticket->response_minutes <= 240 ? 'bg-warning' : 'bg-danger') }}">
                                    @if($ticket->response_minutes < 60)
                                        {{ $ticket->response_minutes }} min
                                    @else
                                        {{ round($ticket->response_minutes / 60, 1) }}h
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($ticket->response_minutes <= 60)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($ticket->response_minutes <= 240)
                                    <span class="badge bg-warning">Good</span>
                                @elseif($ticket->response_minutes <= 1440)
                                    <span class="badge bg-info">Average</span>
                                @else
                                    <span class="badge bg-danger">Poor</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No response time data available for the selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/vendor/chart.js')
<script>
// Response Time Chart
const ctx = document.getElementById('responseTimeChart').getContext('2d');

// Prepare data for histogram
const responseData = @json($responseTimeData->pluck('response_minutes'));
const bins = [
    {label: '0-30 min', min: 0, max: 30, color: 'rgba(75, 192, 192, 0.8)'},
    {label: '30-60 min', min: 30, max: 60, color: 'rgba(54, 162, 235, 0.8)'},
    {label: '1-4 hours', min: 60, max: 240, color: 'rgba(255, 206, 86, 0.8)'},
    {label: '4-24 hours', min: 240, max: 1440, color: 'rgba(255, 159, 64, 0.8)'},
    {label: '> 24 hours', min: 1440, max: Infinity, color: 'rgba(255, 99, 132, 0.8)'}
];

const binCounts = bins.map(bin => {
    return responseData.filter(time => time >= bin.min && time < bin.max).length;
});

const responseTimeChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: bins.map(bin => bin.label),
        datasets: [{
            label: 'Number of Tickets',
            data: binCounts,
            backgroundColor: bins.map(bin => bin.color),
            borderColor: bins.map(bin => bin.color.replace('0.8', '1')),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                },
                title: {
                    display: true,
                    text: 'Number of Tickets'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Response Time'
                }
            }
        }
    }
});
</script>
@endpush
@endsection
