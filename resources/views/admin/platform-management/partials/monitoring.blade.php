<div class="row">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-chart-area me-2"></i>Platform Monitoring Dashboard</h5>
        
        <!-- Real-time Metrics -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Success/Failure Rate Analytics</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="timeRange" id="range1h" value="1" checked>
                            <label class="btn btn-outline-primary" for="range1h">1H</label>
                            <input type="radio" class="btn-check" name="timeRange" id="range24h" value="24">
                            <label class="btn btn-outline-primary" for="range24h">24H</label>
                            <input type="radio" class="btn-check" name="timeRange" id="range7d" value="168">
                            <label class="btn btn-outline-primary" for="range7d">7D</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="successRateChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">Platform Health Status</h6>
                    </div>
                    <div class="card-body" id="platform-health-status">
                        <!-- Platform health indicators will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Time Analytics -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Response Time Analytics</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="responseTimeChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Performance Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Platform Performance Metrics</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="platformManagement.refreshMetrics()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="platform-metrics-table">
                                <thead>
                                    <tr>
                                        <th>Platform</th>
                                        <th>Status</th>
                                        <th>Success Rate</th>
                                        <th>Avg Response Time</th>
                                        <th>Total Requests</th>
                                        <th>Last Success</th>
                                        <th>Last Failure</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="platform-metrics-tbody">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Analytics -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Error Distribution</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="errorDistributionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Error Log</h6>
                    </div>
                    <div class="card-body">
                        <div class="error-log-container" style="max-height: 300px; overflow-y: auto;">
                            <div id="recent-errors">
                                <!-- Recent errors will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Availability Timeline -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Platform Availability Timeline (Last 24 Hours)</h6>
                    </div>
                    <div class="card-body">
                        <div id="availability-timeline">
                            <!-- Timeline will be generated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Configuration -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Alert Thresholds</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Critical Success Rate (%)</label>
                                <input type="number" class="form-control" id="critical-success-rate" 
                                       value="50" min="0" max="100">
                                <small class="text-muted">Below this rate triggers critical alerts</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Warning Success Rate (%)</label>
                                <input type="number" class="form-control" id="warning-success-rate" 
                                       value="80" min="0" max="100">
                                <small class="text-muted">Below this rate triggers warnings</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Max Response Time (ms)</label>
                                <input type="number" class="form-control" id="max-response-time" 
                                       value="5000" min="1000" max="30000">
                                <small class="text-muted">Above this triggers alerts</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Alert Actions</label>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm" onclick="platformManagement.saveAlertThresholds()">
                                        <i class="fas fa-save"></i> Save Thresholds
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="platformManagement.testAlerts()">
                                        <i class="fas fa-bell"></i> Test Alerts
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Platform Detail Modal -->
<div class="modal fade" id="platformDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="platformDetailModalLabel">Platform Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="platformDetailModalBody">
                <!-- Platform details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="platformManagement.runHealthCheck()">
                    <i class="fas fa-heartbeat"></i> Run Health Check
                </button>
            </div>
        </div>
    </div>
</div>
