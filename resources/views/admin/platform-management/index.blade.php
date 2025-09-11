@extends('layouts.modern')
@section('title', 'Platform Management - Sports Ticket Monitoring System')

@section('content')
  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-gradient-primary text-white">
            <h1 class="h4 mb-0">
              <i class="fas fa-cogs me-2"></i>
              Scraping Platform Management Interface
            </h1>
            <p class="mb-0 small">Comprehensive Sports Events Entry Tickets Monitoring, Scraping and Purchase System</p>
          </div>
          <div class="card-body">
            <!-- Platform Status Overview -->
            <div class="row mb-4" id="platform-overview">
              <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="h5 mb-1" id="active-platforms">{{ $platformStats['active'] ?? 0 }}</div>
                        <div class="small">Active Platforms</div>
                      </div>
                      <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="h5 mb-1" id="warning-platforms">{{ $platformStats['warnings'] ?? 0 }}</div>
                        <div class="small">Warnings</div>
                      </div>
                      <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-danger text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="h5 mb-1" id="critical-platforms">{{ $platformStats['critical'] ?? 0 }}</div>
                        <div class="small">Critical Issues</div>
                      </div>
                      <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="h5 mb-1" id="total-requests">{{ $platformStats['total_requests'] ?? 0 }}</div>
                        <div class="small">Total Requests (24h)</div>
                      </div>
                      <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="managementTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="platform-config-tab" data-bs-toggle="tab"
                  data-bs-target="#platform-config" type="button" role="tab">
                  <i class="fas fa-cog me-1"></i>Platform Configuration
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="monitoring-tab" data-bs-toggle="tab" data-bs-target="#monitoring"
                  type="button" role="tab">
                  <i class="fas fa-chart-area me-1"></i>Monitoring Dashboard
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="queue-tab" data-bs-toggle="tab" data-bs-target="#queue" type="button"
                  role="tab">
                  <i class="fas fa-list me-1"></i>Job Queue
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="health-tab" data-bs-toggle="tab" data-bs-target="#health" type="button"
                  role="tab">
                  <i class="fas fa-heartbeat me-1"></i>Health Checks
                </button>
              </li>
            </ul>

            <div class="tab-content mt-4" id="managementTabsContent">
              <!-- Platform Configuration Tab -->
              <div class="tab-pane fade show active" id="platform-config" role="tabpanel">
                @include('admin.platform-management.partials.configuration')
              </div>

              <!-- Monitoring Dashboard Tab -->
              <div class="tab-pane fade" id="monitoring" role="tabpanel">
                @include('admin.platform-management.partials.monitoring')
              </div>

              <!-- Job Queue Tab -->
              <div class="tab-pane fade" id="queue" role="tabpanel">
                @include('admin.platform-management.partials.queue')
              </div>

              <!-- Health Checks Tab -->
              <div class="tab-pane fade" id="health" role="tabpanel">
                @include('admin.platform-management.partials.health')
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Modal -->
  <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <div class="mt-2">Processing...</div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <link href="{{ asset('/assets/css/platform-management.css') }}" rel="stylesheet">
@endpush

@push('scripts')
  @vite('resources/js/admin/platform-management.js')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      window.platformManagement = new PlatformManagement({
        updateInterval: 30000,
        endpoints: {
          stats: '{{ route('admin.platform-management.stats') }}',
          config: '{{ route('admin.platform-management.config') }}',
          health: '{{ route('admin.platform-management.health') }}',
          queue: '{{ route('admin.platform-management.queue') }}'
        }
      });
    });
  </script>
@endpush
