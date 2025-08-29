<div class="row">
  <div class="col-12">
    <h5 class="mb-3"><i class="fas fa-list me-2"></i>Scraping Job Queue Visualization</h5>

    <!-- Queue Status Overview -->
    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <div class="h5 mb-1" id="pending-jobs">{{ $queueStats['pending'] ?? 0 }}</div>
                <div class="small">Pending Jobs</div>
              </div>
              <i class="fas fa-clock fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <div class="h5 mb-1" id="processing-jobs">{{ $queueStats['processing'] ?? 0 }}</div>
                <div class="small">Processing</div>
              </div>
              <i class="fas fa-spinner fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <div class="h5 mb-1" id="completed-jobs">{{ $queueStats['completed'] ?? 0 }}</div>
                <div class="small">Completed (24h)</div>
              </div>
              <i class="fas fa-check fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <div class="h5 mb-1" id="failed-jobs">{{ $queueStats['failed'] ?? 0 }}</div>
                <div class="small">Failed Jobs</div>
              </div>
              <i class="fas fa-times fa-2x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Queue Management Controls -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Queue Management</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="btn-group me-2" role="group">
                  <button class="btn btn-success" onclick="platformManagement.startQueue()">
                    <i class="fas fa-play"></i> Start Queue
                  </button>
                  <button class="btn btn-warning" onclick="platformManagement.pauseQueue()">
                    <i class="fas fa-pause"></i> Pause Queue
                  </button>
                  <button class="btn btn-info" onclick="platformManagement.flushQueue()">
                    <i class="fas fa-broom"></i> Flush Queue
                  </button>
                </div>
                <button class="btn btn-danger" onclick="platformManagement.retryFailedJobs()">
                  <i class="fas fa-redo"></i> Retry Failed Jobs
                </button>
              </div>
              <div class="col-md-6 text-end">
                <div class="d-inline-flex align-items-center me-3">
                  <span class="me-2">Auto Refresh:</span>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                  </div>
                </div>
                <button class="btn btn-outline-primary" onclick="platformManagement.refreshQueue()">
                  <i class="fas fa-sync-alt"></i> Refresh
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Job Queue Tabs -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="queueTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                  type="button" role="tab">
                  <i class="fas fa-clock me-1"></i>Pending Jobs
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing"
                  type="button" role="tab">
                  <i class="fas fa-spinner me-1"></i>Processing
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed"
                  type="button" role="tab">
                  <i class="fas fa-check me-1"></i>Completed
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed"
                  type="button" role="tab">
                  <i class="fas fa-times me-1"></i>Failed
                </button>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="queueTabsContent">
              <!-- Pending Jobs Tab -->
              <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead>
                      <tr>
                        <th>Job ID</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Created</th>
                        <th>Attempts</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="pending-jobs-tbody">
                      <!-- Jobs will be loaded via AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Processing Jobs Tab -->
              <div class="tab-pane fade" id="processing" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead>
                      <tr>
                        <th>Job ID</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Started</th>
                        <th>Duration</th>
                        <th>Progress</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="processing-jobs-tbody">
                      <!-- Jobs will be loaded via AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Completed Jobs Tab -->
              <div class="tab-pane fade" id="completed" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead>
                      <tr>
                        <th>Job ID</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Completed</th>
                        <th>Duration</th>
                        <th>Results</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="completed-jobs-tbody">
                      <!-- Jobs will be loaded via AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Failed Jobs Tab -->
              <div class="tab-pane fade" id="failed" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead>
                      <tr>
                        <th>Job ID</th>
                        <th>Platform</th>
                        <th>Type</th>
                        <th>Failed</th>
                        <th>Error</th>
                        <th>Attempts</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="failed-jobs-tbody">
                      <!-- Jobs will be loaded via AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Queue Performance Chart -->
    <div class="row mt-4">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Queue Throughput (Jobs/Hour)</h6>
          </div>
          <div class="card-body">
            <div x-data="queueChart()" x-init="init()">
              <canvas id="queueThroughputChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Platform Job Distribution</h6>
          </div>
          <div class="card-body">
            <div x-data="errorChart()" x-init="init()">
              <canvas id="platformJobDistributionChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Job Detail Modal -->
<div class="modal fade" id="jobDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Job Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="jobDetailModalBody">
        <!-- Job details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning" onclick="platformManagement.retryJob()">
          <i class="fas fa-redo"></i> Retry Job
        </button>
        <button type="button" class="btn btn-danger" onclick="platformManagement.deleteJob()">
          <i class="fas fa-trash"></i> Delete Job
        </button>
      </div>
    </div>
  </div>
</div>
