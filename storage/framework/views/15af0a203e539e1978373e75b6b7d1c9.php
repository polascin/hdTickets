

<?php $__env->startSection('title', 'System Management'); ?>

<?php $__env->startSection('content'); ?>
<?php if(!Auth::user() || !Auth::user()->canManageSystem()): ?>
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Denied</h3>
            <p class="text-gray-600 mb-4">You don't have permission to access system management.</p>
            <a href="<?php echo e(route('dashboard')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Return to Dashboard
            </a>
        </div>
    </div>
<?php else: ?>
<div class="py-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Management</h1>
                    <p class="mt-2 text-gray-600">Monitor system health, configuration, and maintenance tasks</p>
                </div>
                <div class="flex space-x-4">
                    <button id="refreshBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- System Health Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">System Status</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="systemStatus"><?php echo e($systemHealth['status'] ?? 'Unknown'); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Database</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="dbStatus">Connected</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Cache</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="cacheStatus">Active</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Queue</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="queueStatus">Running</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- System Configuration -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">System Configuration</h3>
                <p class="text-gray-600 mb-4">Manage application settings and preferences</p>
                <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700" onclick="openConfigModal()">
                    Configure System
                </button>
            </div>

            <!-- Cache Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Cache Management</h3>
                <p class="text-gray-600 mb-4">Clear application caches and compiled files</p>
                <button class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700" onclick="openCacheModal()">
                    Clear Caches
                </button>
            </div>

            <!-- Maintenance Tasks -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Maintenance</h3>
                <p class="text-gray-600 mb-4">Run system optimization and maintenance</p>
                <button class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700" onclick="openMaintenanceModal()">
                    Run Maintenance
                </button>
            </div>

            <!-- System Logs -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">System Logs</h3>
                <p class="text-gray-600 mb-4">View application logs and error reports</p>
                <button class="w-full bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700" onclick="openLogsModal()">
                    View Logs
                </button>
            </div>

            <!-- Database Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Database Information</h3>
                <p class="text-gray-600 mb-4">View database statistics and table info</p>
                <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700" onclick="openDatabaseModal()">
                    Database Info
                </button>
            </div>

            <!-- Disk Usage -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Disk Usage</h3>
                <p class="text-gray-600 mb-4">Monitor storage space and file usage</p>
                <button class="w-full bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700" onclick="openDiskModal()">
                    Check Usage
                </button>
            </div>
        </div>

        <!-- System Services Status -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">System Services</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="servicesGrid">
                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full <?php echo e($service['status'] === 'running' ? 'bg-green-500' : 'bg-red-500'); ?> mr-3"></div>
                            <span class="font-medium"><?php echo e($service['name']); ?></span>
                        </div>
                        <span class="text-sm text-gray-500"><?php echo e($service['uptime']); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals will be added via JavaScript -->
<div id="modalContainer"></div>

<script>
let systemData = {
    health: <?php echo json_encode($systemHealth ?? [], 15, 512) ?>,
    config: <?php echo json_encode($systemConfig ?? [], 15, 512) ?>,
    logs: <?php echo json_encode($logs ?? [], 15, 512) ?>,
    services: <?php echo json_encode($services ?? [], 15, 512) ?>
};

// System management functions
function openConfigModal() {
    showModal('System Configuration', createConfigForm());
}

function openCacheModal() {
    showModal('Cache Management', createCacheForm());
}

function openMaintenanceModal() {
    showModal('Maintenance Tasks', createMaintenanceForm());
}

function openLogsModal() {
    showModal('System Logs', createLogsView());
}

function openDatabaseModal() {
    loadDatabaseInfo();
}

function openDiskModal() {
    loadDiskUsage();
}

function showModal(title, content) {
    const modalHtml = `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">${title}</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-content">
                        ${content}
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('modalContainer').innerHTML = modalHtml;
}

function closeModal() {
    document.getElementById('modalContainer').innerHTML = '';
}

function createConfigForm() {
    return `
        <form id="configForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Application Timezone</label>
                <select name="app_timezone" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">Eastern</option>
                    <option value="America/Los_Angeles">Pacific</option>
                    <option value="Europe/London">London</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Session Lifetime (minutes)</label>
                <input type="number" name="session_lifetime" value="120" min="1" max="10080" 
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="maintenance_mode" class="rounded">
                <label class="ml-2 text-sm text-gray-700">Enable Maintenance Mode</label>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Configuration</button>
            </div>
        </form>
    `;
}

function createCacheForm() {
    return `
        <form id="cacheForm" class="space-y-4">
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" name="types" value="config" class="rounded">
                    <span class="ml-2">Configuration Cache</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="types" value="route" class="rounded">
                    <span class="ml-2">Route Cache</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="types" value="view" class="rounded">
                    <span class="ml-2">View Cache</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="types" value="cache" class="rounded">
                    <span class="ml-2">Application Cache</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="types" value="compiled" class="rounded">
                    <span class="ml-2">Compiled Classes</span>
                </label>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Clear Selected</button>
            </div>
        </form>
    `;
}

function createMaintenanceForm() {
    return `
        <form id="maintenanceForm" class="space-y-4">
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" name="tasks" value="optimize" class="rounded">
                    <span class="ml-2">Optimize Application</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="tasks" value="queue_restart" class="rounded">
                    <span class="ml-2">Restart Queue Workers</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="tasks" value="migrate" class="rounded">
                    <span class="ml-2">Run Database Migrations</span>
                </label>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-sm text-yellow-800">⚠️ These tasks may temporarily affect system performance.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Run Tasks</button>
            </div>
        </form>
    `;
}

function createLogsView() {
    return `
        <div class="space-y-4">
            <div class="flex space-x-4">
                <select id="logLevel" class="border border-gray-300 rounded px-3 py-2">
                    <option value="">All Levels</option>
                    <option value="error">Errors</option>
                    <option value="warning">Warnings</option>
                    <option value="info">Info</option>
                    <option value="debug">Debug</option>
                </select>
                <button onclick="refreshLogs()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Refresh</button>
            </div>
            <div id="logsContainer" class="bg-gray-900 text-green-400 p-4 rounded h-96 overflow-y-auto font-mono text-sm">
                Loading logs...
            </div>
        </div>
    `;
}

function loadDatabaseInfo() {
    fetch('/admin/system/database-info')
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold">Database Version</h4>
                            <p>${data.version || 'N/A'}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Total Size</h4>
                            <p>${data.total_size || 'N/A'}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Tables</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rows</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Engine</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${data.tables ? data.tables.map(table => `
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${table.name}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${table.rows}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${table.size}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${table.engine}</td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="4" class="text-center py-4">No data available</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            showModal('Database Information', content);
        });
}

// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh system status
    setInterval(refreshSystemStatus, 30000);
    
    // Form handlers
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'configForm') {
            e.preventDefault();
            handleConfigSubmit(e.target);
        } else if (e.target.id === 'cacheForm') {
            e.preventDefault();
            handleCacheSubmit(e.target);
        } else if (e.target.id === 'maintenanceForm') {
            e.preventDefault();
            handleMaintenanceSubmit(e.target);
        }
    });
});

function refreshSystemStatus() {
    fetch('/admin/system/health')
        .then(response => response.json())
        .then(data => {
            document.getElementById('systemStatus').textContent = data.status || 'Unknown';
            // Update other status indicators
        });
}

function handleConfigSubmit(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('/admin/system/configuration', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Configuration updated successfully!');
            closeModal();
        }
    });
}

function handleCacheSubmit(form) {
    const formData = new FormData(form);
    const types = formData.getAll('types');
    
    fetch('/admin/system/cache/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ types })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Cache cleared successfully!');
            closeModal();
        }
    });
}

function handleMaintenanceSubmit(form) {
    const formData = new FormData(form);
    const tasks = formData.getAll('tasks');
    
    fetch('/admin/system/maintenance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ tasks })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Maintenance tasks completed!');
            closeModal();
        }
    });
}
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\admin\system\index.blade.php ENDPATH**/ ?>