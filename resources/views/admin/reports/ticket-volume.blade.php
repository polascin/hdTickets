@extends('layouts.app-v2')

@section('title', 'Ticket Volume Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Ticket Volume</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Ticket Volume Report</h1>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'tickets', 'format' => 'csv']) }}" class="btn btn-outline-primary">
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
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="group_by" class="form-label">Group By</label>
                    <select name="group_by" id="group_by" class="form-control">
                        <option value="day" {{ request('group_by', 'day') == 'day' ? 'selected' : '' }}>Daily</option>
                        <option value="week" {{ request('group_by') == 'week' ? 'selected' : '' }}>Weekly</option>
                        <option value="month" {{ request('group_by') == 'month' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Volume Trends</h6>
        </div>
        <div class="card-body">
            <canvas id="volumeChart" height="100"></canvas>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Detailed Data</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Total Tickets</th>
                            <th>Open Tickets</th>
                            <th>Resolved Tickets</th>
                            <th>High Priority</th>
                            <th>Resolution Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr>
                            <td>{{ $row->period }}</td>
                            <td>
                                <span class="badge bg-primary">{{ number_format($row->total) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ number_format($row->open) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ number_format($row->resolved) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">{{ number_format($row->high_priority) }}</span>
                            </td>
                            <td>
                                @if($row->total > 0)
                                    {{ round(($row->resolved / $row->total) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No data available for the selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Volume Chart
const ctx = document.getElementById('volumeChart').getContext('2d');
const volumeChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($data->pluck('period')),
        datasets: [{
            label: 'Total Tickets',
            data: @json($data->pluck('total')),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            fill: true,
            tension: 0.1
        }, {
            label: 'Open Tickets',
            data: @json($data->pluck('open')),
            borderColor: 'rgb(255, 206, 86)',
            backgroundColor: 'rgba(255, 206, 86, 0.1)',
            fill: false,
            tension: 0.1
        }, {
            label: 'Resolved Tickets',
            data: @json($data->pluck('resolved')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
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
@endsection
