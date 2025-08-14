<div class="row">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-heartbeat me-2"></i>Platform Health Checks</h5>
        
        <!-- Health Check Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Run Health Checks</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="platformManagement.runAllHealthChecks()">
                            <i class="fas fa-stethoscope"></i> Run All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Checks Overview -->
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4" v-for="platform in platforms">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i :class="`fas fa-${platform.icon} me-2`"></i>{{ platform.name }}</h6>
                        <button class="btn btn-sm btn-outline-secondary" v-if="!platform.checking" @click="runHealthCheck(platform.name)">
                            <i class="fas fa-sync"></i> Check
                        </button>
                        <div v-else class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="platform.health">
                            <div class="text-center">
                                <i :class="platform.healthIcon" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">{{ platform.healthStatus }}</h5>
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <i class="fas fa-ban text-muted" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Not Checked</h5>
                        </div>
                        <hr>
                        <div>
                            <strong>Last Checked:</strong> {{ platform.lastChecked ? platform.lastChecked : 'Never' }}<br>
                            <strong>Issues Found:</strong> {{ platform.issues || 'None' }}<br>
                            <strong>Suggestions:</strong> {{ platform.suggestions || 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

