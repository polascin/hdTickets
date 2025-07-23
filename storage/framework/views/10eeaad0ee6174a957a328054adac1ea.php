

<?php $__env->startSection('title', 'Scraping Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Scraping Management</h1>
                    <p class="mt-2 text-gray-600">Monitor and manage sports ticket scraping operations across all platforms</p>
                </div>
                <div class="flex space-x-4">
                    <button id="refreshBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <button id="testRotationBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Test Rotation
                    </button>
                </div>
            </div>
        </div>

        <!-- Scraping Statistics Cards -->
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
                        <h3 class="text-sm font-medium text-gray-500">Total Operations (24h)</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="totalOperations"><?php echo e($stats['total_operations'] ?? 0); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Success Rate</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="successRate"><?php echo e($stats['success_rate'] ?? 0); ?>%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Avg Response Time</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="avgResponseTime"><?php echo e($stats['avg_response_time'] ?? 0); ?>ms</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Active Users</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="activeUsers"><?php echo e($userRotationStats['active_users'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Performance -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Platform Performance</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="platformsGrid">
                        <?php $__currentLoopData = $platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $platform => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-lg"><?php echo e($data['name']); ?></h4>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo e($data['success_rate'] > 80 ? 'bg-green-100 text-green-800' : ($data['success_rate'] > 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')); ?>">
                                    <?php echo e($data['success_rate']); ?>%
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Operations:</span>
                                    <span><?php echo e($data['total_operations']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Successful:</span>
                                    <span><?php echo e($data['successful_operations']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Avg Response:</span>
                                    <span><?php echo e($data['avg_response_time']); ?>ms</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Results:</span>
                                    <span><?php echo e(number_format($data['total_results'])); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Dedicated Users:</span>
                                    <span><?php echo e($data['dedicated_users']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tools -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- User Rotation Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">User Rotation</h3>
                <p class="text-gray-600 mb-4">Manage scraping user rotation and testing</p>
                <div class="space-y-2">
                    <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700" onclick="openRotationModal()">
                        View Rotation Stats
                    </button>
                    <button class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700" onclick="testUserRotation()">
                        Test Rotation
                    </button>
                </div>
            </div>

            <!-- Scraping Configuration -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Configuration</h3>
                <p class="text-gray-600 mb-4">Adjust scraping parameters and settings</p>
                <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700" onclick="openConfigModal()">
                    Manage Configuration
                </button>
            </div>

            <!-- Performance Monitoring -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Performance</h3>
                <p class="text-gray-600 mb-4">Monitor scraping performance metrics</p>
                <button class="w-full bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700" onclick="openPerformanceModal()">
                    View Metrics
                </button>
            </div>
        </div>

        <!-- Recent Operations -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Operations</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Results</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="recentOperations">
                            <?php $__currentLoopData = $recentOperations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e(ucfirst($operation['platform'])); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($operation['operation']); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($operation['status'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo e(ucfirst($operation['status'])); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($operation['response_time']); ?>ms
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($operation['results_count'] ?? 0); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($operation['formatted_time']); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals will be added via JavaScript -->
<div id="modalContainer"></div>

<script>
let scrapingData = {
    stats: <?php echo json_encode($stats ?? [], 15, 512) ?>,
    platforms: <?php echo json_encode($platforms ?? [], 15, 512) ?>,
    recentOperations: <?php echo json_encode($recentOperations ?? [], 15, 512) ?>,
    userRotationStats: <?php echo json_encode($userRotationStats ?? [], 15, 512) ?>
};

// Refresh data
document.getElementById('refreshBtn').addEventListener('click', function() {
    location.reload();
});

// Test rotation button
document.getElementById('testRotationBtn').addEventListener('click', function() {
    testUserRotation();
});

// Scraping management functions
function openRotationModal() {
    showModal('User Rotation Statistics', createRotationView());
}

function openConfigModal() {
    showModal('Scraping Configuration', createConfigForm());
}

function openPerformanceModal() {
    showModal('Performance Metrics', createPerformanceView());
}

function testUserRotation() {
    if (confirm('Test user rotation system?')) {
        fetch('<?php echo e(route('admin.scraping.rotation-test')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({
                count: 5
            })
        })
        .then(response => response.json())
        .then(data => {
            showModal('Rotation Test Results', createRotationTestResults(data));
        })
        .catch(error => {
            alert('Error testing rotation: ' + error.message);
        });
    }
}

function createRotationView() {
    return `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded">
                    <h4 class="font-semibold">Total Users</h4>
                    <p class="text-2xl">${scrapingData.userRotationStats.total_users || 0}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <h4 class="font-semibold">Active Users</h4>
                    <p class="text-2xl">${scrapingData.userRotationStats.active_users || 0}</p>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-semibold mb-2">User Distribution</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Premium Customers:</span>
                        <span>${scrapingData.userRotationStats.premium_customers || 0}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Platform Agents:</span>
                        <span>${scrapingData.userRotationStats.platform_agents || 0}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Rotation Pool:</span>
                        <span>${scrapingData.userRotationStats.rotation_pool || 0}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createConfigForm() {
    return `
        <form id="configForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Concurrent Requests</label>
                <input type="number" name="max_concurrent_requests" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" max="100" value="10">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Request Delay (ms)</label>
                <input type="number" name="request_delay_ms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" max="10000" value="1000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Retry Attempts</label>
                <input type="number" name="retry_attempts" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" max="10" value="3">
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="user_rotation_enabled" class="rounded border-gray-300" checked>
                    <span class="ml-2 text-sm text-gray-700">Enable User Rotation</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Platform Rotation Interval (seconds)</label>
                <input type="number" name="platform_rotation_interval" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" max="3600" value="300">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Configuration</button>
            </div>
        </form>
    `;
}

function createPerformanceView() {
    return `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                ${Object.entries(scrapingData.platforms).map(([platform, data]) => `
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-semibold">${data.name}</h4>
                        <div class="text-sm space-y-1">
                            <div>Success Rate: ${data.success_rate}%</div>
                            <div>Operations: ${data.total_operations}</div>
                            <div>Avg Time: ${data.avg_response_time}ms</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function createRotationTestResults(data) {
    return `
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded">
                <h4 class="font-semibold">Test Summary</h4>
                <div class="text-sm space-y-1">
                    <div>Total Attempts: ${data.summary.total_attempts}</div>
                    <div>Successful Rotations: ${data.summary.successful_rotations}</div>
                    <div>Success Rate: ${data.summary.success_rate.toFixed(2)}%</div>
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-2">Test Results</h4>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    ${data.test_results.map(result => `
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span>Attempt ${result.attempt}</span>
                            <span class="text-sm">${result.user_email || 'No user'}</span>
                            <span class="px-2 py-1 text-xs rounded ${result.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${result.success ? 'Success' : 'Failed'}
                            </span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}

// Modal functions
function showModal(title, content) {
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">${title}</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div>${content}</div>
            </div>
        </div>
    `;
}

function closeModal() {
    document.getElementById('modalContainer').innerHTML = '';
}

// Handle configuration form submission
document.addEventListener('submit', function(e) {
    if (e.target.id === 'configForm') {
        e.preventDefault();
        const formData = new FormData(e.target);
        const config = Object.fromEntries(formData.entries());
        
        fetch('<?php echo e(route('admin.scraping.configuration.update')); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify(config)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Configuration updated successfully!');
                closeModal();
            } else {
                alert('Error updating configuration');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\admin\scraping\index.blade.php ENDPATH**/ ?>