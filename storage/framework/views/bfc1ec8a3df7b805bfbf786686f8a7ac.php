<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Purchase Decision Dashboard')); ?>

            </h2>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('purchase-decisions.select-tickets')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Select Tickets
                </a>
                <button onclick="refreshStats()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Refresh
                </button>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Queued</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e($stats['total_queued']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Processing</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e($stats['processing']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Today</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e($stats['completed_today']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Success Rate</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e($stats['success_rate']); ?>%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Statuses</option>
                                <option value="queued" <?php echo e(request('status') === 'queued' ? 'selected' : ''); ?>>Queued</option>
                                <option value="processing" <?php echo e(request('status') === 'processing' ? 'selected' : ''); ?>>Processing</option>
                                <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed</option>
                                <option value="failed" <?php echo e(request('status') === 'failed' ? 'selected' : ''); ?>>Failed</option>
                                <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Priorities</option>
                                <option value="critical" <?php echo e(request('priority') === 'critical' ? 'selected' : ''); ?>>Critical</option>
                                <option value="urgent" <?php echo e(request('priority') === 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                                <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>>High</option>
                                <option value="medium" <?php echo e(request('priority') === 'medium' ? 'selected' : ''); ?>>Medium</option>
                                <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>>Low</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                            <select name="platform" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Platforms</option>
                                <option value="ticketmaster" <?php echo e(request('platform') === 'ticketmaster' ? 'selected' : ''); ?>>Ticketmaster</option>
                                <option value="stubhub" <?php echo e(request('platform') === 'stubhub' ? 'selected' : ''); ?>>StubHub</option>
                                <option value="viagogo" <?php echo e(request('platform') === 'viagogo' ? 'selected' : ''); ?>>Viagogo</option>
                                <option value="tickpick" <?php echo e(request('platform') === 'tickpick' ? 'selected' : ''); ?>>TickPick</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selected By</label>
                            <select name="selected_by" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Users</option>
                                <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($agent->id); ?>" <?php echo e(request('selected_by') == $agent->id ? 'selected' : ''); ?>>
                                        <?php echo e($agent->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Purchase Queue Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Purchase Queue</h3>
                        <div class="flex space-x-2">
                            <button onclick="toggleBulkActions()" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Bulk Actions
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Actions (Initially Hidden) -->
                    <div id="bulk-actions" class="hidden mb-4 p-4 bg-gray-50 rounded-lg">
                        <form method="POST" action="<?php echo e(route('purchase-decisions.bulk-action')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="flex items-center space-x-4">
                                <select name="action" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Action</option>
                                    <option value="cancel">Cancel Selected</option>
                                    <option value="update_priority">Update Priority</option>
                                    <option value="process">Process Selected</option>
                                </select>
                                
                                <select name="priority" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" style="display: none;" id="priority-select">
                                    <option value="critical">Critical</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                                
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Apply
                                </button>
                            </div>
                            <div id="selected-items"></div>
                        </form>
                    </div>

                    <?php if($purchaseQueue->isEmpty()): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a2 2 0 00-2-2H4a2 2 0 00-2 2v1z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No items in purchase queue</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by selecting tickets for automated purchasing.</p>
                            <div class="mt-6">
                                <a href="<?php echo e(route('purchase-decisions.select-tickets')); ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Select Tickets
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selected By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $purchaseQueue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" name="queue_ids[]" value="<?php echo e($item->id); ?>" class="queue-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo e(Str::limit($item->scrapedTicket->event_title, 40)); ?>

                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo e($item->scrapedTicket->platform_display_name); ?> • 
                                                            <?php echo e($item->scrapedTicket->venue); ?>

                                                        </div>
                                                        <?php if($item->scrapedTicket->is_high_demand): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                High Demand
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($item->status_color); ?>-100 text-<?php echo e($item->status_color); ?>-800">
                                                    <?php echo e(ucfirst($item->status)); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($item->priority_color); ?>-100 text-<?php echo e($item->priority_color); ?>-800">
                                                    <?php echo e(ucfirst($item->priority)); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div><?php echo e($item->scrapedTicket->formatted_price); ?></div>
                                                <?php if($item->max_price): ?>
                                                    <div class="text-xs text-gray-500">Max: <?php echo e($item->scrapedTicket->currency); ?> <?php echo e(number_format($item->max_price, 2)); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo e($item->selectedByUser->name); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo e($item->created_at->diffForHumans()); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="<?php echo e(route('purchase-decisions.show', $item)); ?>" class="text-indigo-600 hover:text-indigo-900">
                                                        View
                                                    </a>
                                                    
                                                    <?php if($item->status === 'queued'): ?>
                                                        <form method="POST" action="<?php echo e(route('purchase-decisions.process', $item)); ?>" class="inline">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                                Process
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($item->isActive()): ?>
                                                        <form method="POST" action="<?php echo e(route('purchase-decisions.cancel', $item)); ?>" class="inline">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this item?')">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <?php echo e($purchaseQueue->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulk-actions');
            bulkActions.classList.toggle('hidden');
        }

        // Handle select all checkbox
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.queue-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedItems();
        });

        // Handle individual checkboxes
        document.querySelectorAll('.queue-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedItems);
        });

        // Show priority select when update_priority action is selected
        document.querySelector('select[name="action"]').addEventListener('change', function() {
            const prioritySelect = document.getElementById('priority-select');
            if (this.value === 'update_priority') {
                prioritySelect.style.display = 'block';
            } else {
                prioritySelect.style.display = 'none';
            }
        });

        function updateSelectedItems() {
            const selectedCheckboxes = document.querySelectorAll('.queue-checkbox:checked');
            const selectedItems = document.getElementById('selected-items');
            
            // Clear existing hidden inputs
            selectedItems.innerHTML = '';
            
            // Add hidden inputs for selected items
            selectedCheckboxes.forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'queue_ids[]';
                hiddenInput.value = checkbox.value;
                selectedItems.appendChild(hiddenInput);
            });
        }

        function refreshStats() {
            location.reload();
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/purchase-decisions/index.blade.php ENDPATH**/ ?>