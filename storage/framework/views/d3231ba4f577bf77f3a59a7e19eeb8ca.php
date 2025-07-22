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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Admin Dashboard')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2">Welcome, <?php echo e(Auth::user()->name); ?>!</h3>
                    <p class="text-gray-600">You are logged in as an <strong>Administrator</strong>. Here's your system overview:</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Tickets -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Tickets</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($totalTickets); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Open Tickets -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Open Tickets</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($openTickets); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- High Priority Tickets -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">High Priority</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($highPriorityTickets); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500">Total Users</div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e($totalUsers); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">User Management</h4>
                    <p class="text-blue-700 text-sm mb-4">Manage users, roles, and permissions</p>
                    <div class="text-xs text-blue-600 mb-3">
                        Agents: <?php echo e($totalAgents); ?> | Customers: <?php echo e($totalCustomers); ?>

                    </div>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700">Manage Users</a>
                </div>
                
                <div class="bg-green-50 border border-green-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-green-900 mb-2">Category Management</h4>
                    <p class="text-green-700 text-sm mb-4">Organize tickets with categories</p>
                    <div class="text-xs text-green-600 mb-3">
                        Active Categories: <?php echo e($totalCategories); ?>

                    </div>
                    <a href="<?php echo e(route('admin.categories.index')); ?>" class="inline-block bg-green-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-700">Manage Categories</a>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-purple-900 mb-2">System Settings</h4>
                    <p class="text-purple-700 text-sm mb-4">Configure system preferences</p>
                    <div class="text-xs text-purple-600 mb-3">
                        Email, notifications, and more
                    </div>
                    <a href="#" class="inline-block bg-purple-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-purple-700">System Settings</a>
                </div>

                <div class="bg-indigo-50 border border-indigo-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-indigo-900 mb-2">Ticket Management</h4>
                    <p class="text-indigo-700 text-sm mb-4">Assign and manage tickets</p>
                    <div class="text-xs text-indigo-600 mb-3">
                        Assignment, escalation, bulk actions
                    </div>
                    <a href="<?php echo e(route('admin.tickets.index')); ?>" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700">Manage Tickets</a>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-yellow-900 mb-2">Reports & Analytics</h4>
                    <p class="text-yellow-700 text-sm mb-4">View system reports and analytics</p>
                    <div class="text-xs text-yellow-600 mb-3">
                        Performance metrics and insights
                    </div>
                    <a href="<?php echo e(route('admin.reports.index')); ?>" class="inline-block bg-yellow-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-yellow-700">View Reports</a>
                </div>

                <div class="bg-red-50 border border-red-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-red-900 mb-2">API Integration</h4>
                    <p class="text-red-700 text-sm mb-4">Connect to ticket platforms</p>
                    <div class="text-xs text-red-600 mb-3">
                        Ticketmaster, SeatGeek, and more
                    </div>
                    <a href="<?php echo e(route('ticket-api.index')); ?>" class="inline-block bg-red-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-700">Manage APIs</a>
                </div>
            </div>

            <!-- Recent Activity -->
            <?php if($recentTickets->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Tickets</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $recentTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#<?php echo e($ticket->id); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e(Str::limit($ticket->title, 30)); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($ticket->user->name); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($ticket->category->name ?? 'Uncategorized'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo e($ticket->status_color); ?>-100 text-<?php echo e($ticket->status_color); ?>-800">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo e($ticket->priority_color); ?>-100 text-<?php echo e($ticket->priority_color); ?>-800">
                                            <?php echo e(ucfirst($ticket->priority)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($ticket->assignedTo->name ?? 'Unassigned'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($ticket->created_at->diffForHumans()); ?>

                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
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
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/dashboard/admin.blade.php ENDPATH**/ ?>