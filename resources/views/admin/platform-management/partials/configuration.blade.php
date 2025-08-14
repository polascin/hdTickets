<div class="row">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-sliders-h me-2"></i>Platform Configuration Management</h5>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-6">
                <button class="btn btn-success me-2" onclick="platformManagement.enableAllPlatforms()">
                    <i class="fas fa-play"></i> Enable All
                </button>
                <button class="btn btn-warning me-2" onclick="platformManagement.disableAllPlatforms()">
                    <i class="fas fa-pause"></i> Disable All
                </button>
                <button class="btn btn-info" onclick="platformManagement.testAllConnections()">
                    <i class="fas fa-satellite-dish"></i> Test All Connections
                </button>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" onclick="platformManagement.importConfig()">
                    <i class="fas fa-upload"></i> Import Config
                </button>
                <button class="btn btn-secondary" onclick="platformManagement.exportConfig()">
                    <i class="fas fa-download"></i> Export Config
                </button>
            </div>
        </div>

        <!-- Platform Configuration Cards -->
        <div class="row" id="platform-config-cards">
            @foreach($platforms ?? [] as $platform => $config)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card platform-config-card" data-platform="{{ $platform }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-{{ $config['icon'] ?? 'globe' }} me-2"></i>
                            {{ $config['name'] ?? ucfirst($platform) }}
                        </h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input platform-toggle" type="checkbox" 
                                   id="toggle-{{ $platform }}" 
                                   data-platform="{{ $platform }}"
                                   {{ ($config['enabled'] ?? false) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- API Endpoint Management -->
                        <div class="mb-3">
                            <label class="form-label small">API Endpoint</label>
                            <div class="input-group">
                                <input type="url" class="form-control form-control-sm" 
                                       name="base_url" 
                                       value="{{ $config['base_url'] ?? '' }}"
                                       placeholder="https://api.example.com">
                                <button class="btn btn-outline-secondary btn-sm" type="button" 
                                        onclick="platformManagement.testEndpoint('{{ $platform }}')">
                                    <i class="fas fa-satellite-dish"></i>
                                </button>
                            </div>
                        </div>

                        <!-- API Key Management -->
                        @if(isset($config['api_key']) || isset($config['client_id']))
                        <div class="mb-3">
                            <label class="form-label small">API Credentials</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-sm" 
                                       name="api_key" 
                                       value="{{ $config['api_key'] ?? $config['client_id'] ?? '' }}"
                                       placeholder="API Key or Client ID">
                                <button class="btn btn-outline-secondary btn-sm" type="button" 
                                        onclick="platformManagement.togglePasswordVisibility(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        @endif

                        <!-- Rate Limiting Configuration -->
                        <div class="mb-3">
                            <label class="form-label small">Rate Limiting</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="requests_per_second" 
                                           value="{{ $config['rate_limit']['requests_per_second'] ?? 1 }}"
                                           placeholder="Req/sec" min="1" max="100">
                                    <small class="text-muted">Requests/sec</small>
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="timeout" 
                                           value="{{ $config['timeout'] ?? 30 }}"
                                           placeholder="Timeout" min="5" max="300">
                                    <small class="text-muted">Timeout (s)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Proxy Settings -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="proxy_rotation" 
                                       id="proxy-{{ $platform }}"
                                       {{ ($config['scraping']['proxy_rotation'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="proxy-{{ $platform }}">
                                    Enable Proxy Rotation
                                </label>
                            </div>
                        </div>

                        <!-- Error Handling Rules -->
                        <div class="mb-3">
                            <label class="form-label small">Error Handling</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="max_retries" 
                                           value="{{ $config['scraping']['max_retries'] ?? 3 }}"
                                           placeholder="Max retries" min="1" max="10">
                                    <small class="text-muted">Max Retries</small>
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="backoff_factor" 
                                           value="{{ $config['scraping']['backoff_factor'] ?? 2 }}"
                                           placeholder="Backoff" min="1" max="5" step="0.1">
                                    <small class="text-muted">Backoff Factor</small>
                                </div>
                            </div>
                        </div>

                        <!-- Status Indicator -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="platform-status">
                                <span class="badge bg-{{ $config['status_color'] ?? 'secondary' }}">
                                    {{ $config['status'] ?? 'Unknown' }}
                                </span>
                            </div>
                            <div class="platform-actions">
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="platformManagement.saveConfig('{{ $platform }}')">
                                    <i class="fas fa-save"></i> Save
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" 
                                        onclick="platformManagement.resetConfig('{{ $platform }}')">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Global Configuration Settings -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-globe me-2"></i>Global Configuration</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Max Concurrent Scrapers</label>
                        <input type="number" class="form-control" name="max_concurrent_scrapers" 
                               value="{{ $globalConfig['max_concurrent_scrapers'] ?? 3 }}" min="1" max="20">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Default Rate Limit (sec)</label>
                        <input type="number" class="form-control" name="default_rate_limit" 
                               value="{{ $globalConfig['default_rate_limit'] ?? 2 }}" min="1" max="60">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cache TTL (minutes)</label>
                        <input type="number" class="form-control" name="cache_ttl_minutes" 
                               value="{{ $globalConfig['cache_ttl_minutes'] ?? 60 }}" min="5" max="1440">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="debug_logging" 
                                   id="debug-logging" {{ ($globalConfig['debug_logging'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="debug-logging">
                                Enable Debug Logging
                            </label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-success" onclick="platformManagement.saveGlobalConfig()">
                            <i class="fas fa-save"></i> Save Global Configuration
                        </button>
                        <button class="btn btn-warning ms-2" onclick="platformManagement.resetGlobalConfig()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
