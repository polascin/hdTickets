@extends('layouts.modern')
@section('title', 'Agent Performance Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Agent Performance</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Agent Performance Report</h1>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'agent_performance', 'format' => 'csv']) }}" class="btn btn-outline-primary">
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

    <!-- Performance Summary -->
    <div class="row mb-4">
        @if(count($agents) > 0)
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Agents
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($agents) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Avg Resolution Rate
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ round(collect($agents)->avg('resolution_rate'), 1) }}%
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Total Resolved
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format(collect($agents)->sum('resolved_tickets')) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Total Assigned
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format(collect($agents)->sum('assigned_tickets')) }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Performance Chart -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Resolution Rate Comparison</h6>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="100"></canvas>
        </div>
    </div>

    <!-- Detailed Performance Table -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Agent Performance Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Email</th>
                            <th>Assigned Tickets</th>
                            <th>Resolved Tickets</th>
                            <th>Resolution Rate</th>
                            <th>Avg Resolution Time</th>
                            <th>First Response Time</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                         style="width: 35px; height: 35px; font-size: 14px;">
                                        {{ strtoupper(substr($agent['name'], 0, 1)) }}
                                    </div>
                                    <strong>{{ $agent['name'] }}</strong>
                                </div>
                            </td>
                            <td>{{ $agent['email'] }}</td>
                            <td>
                                <span class="badge bg-info">{{ number_format($agent['assigned_tickets']) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ number_format($agent['resolved_tickets']) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2">{{ $agent['resolution_rate'] }}%</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar {{ $agent['resolution_rate'] >= 80 ? 'bg-success' : ($agent['resolution_rate'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                             style="width: {{ $agent['resolution_rate'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($agent['avg_resolution_time'] > 0)
                                    <span class="text-muted">{{ $agent['avg_resolution_time'] }}h</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($agent['first_response_time'] > 0)
                                    <span class="text-muted">{{ $agent['first_response_time'] }}h</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($agent['resolution_rate'] >= 80)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($agent['resolution_rate'] >= 60)
                                    <span class="badge bg-warning">Good</span>
                                @elseif($agent['resolution_rate'] >= 40)
                                    <span class="badge bg-info">Average</span>
                                @else
                                    <span class="badge bg-danger">Needs Improvement</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No agent performance data available for the selected period</td>
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
// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json(collect($agents)->pluck('name')),
        datasets: [{
            label: 'Resolution Rate (%)',
            data: @json(collect($agents)->pluck('resolution_rate')),
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Resolved Tickets',
            data: @json(collect($agents)->pluck('resolved_tickets')),
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Resolution Rate (%)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Tickets'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});
</script>
@endpush
@endsection
