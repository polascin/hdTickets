@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
@if(!Auth::user() || !Auth::user()->canManageSystem())
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Denied</h3>
            <p class="text-gray-600 mb-4">You don't have permission to access reports.</p>
            <a href="{{ route('dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Return to Dashboard
            </a>
        </div>
    </div>
@else
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reports Dashboard</h1>
        
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i> Export Reports
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'tickets', 'format' => 'csv']) }}">
                    <i class="fas fa-file-csv"></i> Tickets (CSV)
                </a>
                <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'agent_performance', 'format' => 'csv']) }}">
                    <i class="fas fa-file-csv"></i> Agent Performance (CSV)
                </a>
                <a class="dropdown-item" href="{{ route('admin.reports.export', ['type' => 'category_analysis', 'format' => 'csv']) }}">
                    <i class="fas fa-file-csv"></i> Category Analysis (CSV)
                </a>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tickets
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTickets) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Open Tickets
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($openTickets) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Resolved Tickets
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($resolvedTickets) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue Tickets
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($overdueTickets) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Avg Response Time</div>
                            <div class="h6 mb-0">{{ $avgResponseTime }} hours</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Avg Resolution Time</div>
                            <div class="h6 mb-0">{{ $avgResolutionTime }} hours</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Resolution Rate</div>
                            <div class="h6 mb-0">{{ $resolutionRate }}%</div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $resolutionRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Weekly Ticket Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="weeklyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Agents and Workload -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Agents</h6>
                </div>
                <div class="card-body">
                    @forelse($topAgents as $agent)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $agent->full_name }}</h6>
                            <small class="text-muted">{{ $agent->resolved_tickets }} tickets resolved this month</small>
                        </div>
                        <span class="badge bg-success">{{ $agent->resolved_tickets }}</span>
                    </div>
                    @empty
                    <p class="text-muted">No agent performance data available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Agent Workload</h6>
                </div>
                <div class="card-body">
                    @forelse($agentWorkload as $agent)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $agent->full_name }}</h6>
                            <small class="text-muted">Active tickets assigned</small>
                        </div>
                        <span class="badge {{ $agent->active_tickets > 10 ? 'bg-danger' : ($agent->active_tickets > 5 ? 'bg-warning' : 'bg-success') }}">
                            {{ $agent->active_tickets }}
                        </span>
                    </div>
                    @empty
                    <p class="text-muted">No workload data available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Report Links -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detailed Reports</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.ticket-volume') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-chart-line"></i><br>
                                Ticket Volume Report
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.agent-performance') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-users"></i><br>
                                Agent Performance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.category-analysis') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-tags"></i><br>
                                Category Analysis
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.reports.response-time') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-clock"></i><br>
                                Response Time Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Weekly Trend Chart
const ctx = document.getElementById('weeklyTrendChart').getContext('2d');
const weeklyTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json(collect($weeklyTrend)->pluck('date')),
        datasets: [{
            label: 'Tickets Created',
            data: @json(collect($weeklyTrend)->pluck('tickets')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
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
                }
            }
        }
    }
});
</script>
@endpush
@endif
@endsection
