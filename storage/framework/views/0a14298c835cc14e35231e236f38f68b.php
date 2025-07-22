

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tickets</h1>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Ticket::class)): ?>
        <a href="<?php echo e(route('tickets.create')); ?>" class="btn btn-primary">Create New Ticket</a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('tickets.index')); ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="all">All Statuses</option>
                            <?php $__currentLoopData = \App\Models\Ticket::getStatuses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): ?> selected <?php endif; ?>>
                                    <?php echo e(ucwords(str_replace('_', ' ', $status))); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            <option value="all">All Priorities</option>
                            <?php $__currentLoopData = \App\Models\Ticket::getPriorities(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($priority); ?>" <?php if(request('priority') === $priority): ?> selected <?php endif; ?>>
                                    <?php echo e(ucfirst($priority)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="all">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if(request('category') == $category->id): ?> selected <?php endif; ?>>
                                    <?php echo e($category->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php if(auth()->user()->isAdmin() || auth()->user()->isAgent()): ?>
                    <div class="col-md-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select class="form-select" name="assigned_to">
                            <option value="all">All Assignees</option>
                            <option value="unassigned" <?php if(request('assigned_to') === 'unassigned'): ?> selected <?php endif; ?>>Unassigned</option>
                                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($agent->id); ?>" <?php if(request('assigned_to') == $agent->id): ?> selected <?php endif; ?>>
                                                    <?php echo e(($agent->name ?? 'Unknown') . ($agent->surname ? ' ' . $agent->surname : '')); ?><?php echo e($agent->username ? ' (' . $agent->username . ')' : ''); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search tickets...">
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select class="form-select" name="sort_by">
                            <option value="last_activity_at" <?php if(request('sort_by') === 'last_activity_at'): ?> selected <?php endif; ?>>Last Activity</option>
                            <option value="created_at" <?php if(request('sort_by') === 'created_at'): ?> selected <?php endif; ?>>Created Date</option>
                            <option value="priority" <?php if(request('sort_by') === 'priority'): ?> selected <?php endif; ?>>Priority</option>
                            <option value="status" <?php if(request('sort_by') === 'status'): ?> selected <?php endif; ?>>Status</option>
                            <option value="due_date" <?php if(request('sort_by') === 'due_date'): ?> selected <?php endif; ?>>Due Date</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <select class="form-select" name="sort_order">
                            <option value="desc" <?php if(request('sort_order', 'desc') === 'desc'): ?> selected <?php endif; ?>>Descending</option>
                            <option value="asc" <?php if(request('sort_order') === 'asc'): ?> selected <?php endif; ?>>Ascending</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <button type="submit" class="btn btn-secondary">Apply Filters</button>
                        <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body">
            <?php if($tickets->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Category</th>
                                <th>Created By</th>
                                <?php if(auth()->user()->isAdmin() || auth()->user()->isAgent()): ?>
                                <th>Assigned To</th>
                                <?php endif; ?>
                                <th>Created</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="text-decoration-none">
                                        #<?php echo e($ticket->id); ?>

                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="text-decoration-none">
                                        <?php echo e(Str::limit($ticket->title, 50)); ?>

                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($ticket->status_color); ?>">
                                        <?php echo e(ucwords(str_replace('_', ' ', $ticket->status))); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($ticket->priority_color); ?>">
                                        <?php echo e(ucfirst($ticket->priority)); ?>

                                    </span>
                                </td>
                                <td><?php echo e($ticket->category->name ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($ticket->user): ?>
                                        <?php echo e(($ticket->user->name ?? 'Unknown') . ($ticket->user->surname ? ' ' . $ticket->user->surname : '')); ?>

                                        <?php if($ticket->user->username): ?>
                                            <br><small class="text-muted"><?php echo e($ticket->user->username); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <?php if(auth()->user()->isAdmin() || auth()->user()->isAgent()): ?>
                                <td>
                                    <?php if($ticket->assignedTo): ?>
                                        <?php echo e(($ticket->assignedTo->name ?? 'Unknown') . ($ticket->assignedTo->surname ? ' ' . $ticket->assignedTo->surname : '')); ?>

                                        <?php if($ticket->assignedTo->username): ?>
                                            <br><small class="text-muted"><?php echo e($ticket->assignedTo->username); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Unassigned
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td><?php echo e($ticket->created_at->format('M d, Y')); ?></td>
                                <td><?php echo e($ticket->last_activity_at->diffForHumans()); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-outline-primary">View</a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $ticket)): ?>
                                        <a href="<?php echo e(route('tickets.edit', $ticket)); ?>" class="btn btn-outline-secondary">Edit</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing <?php echo e($tickets->firstItem()); ?> to <?php echo e($tickets->lastItem()); ?> of <?php echo e($tickets->total()); ?> results
                    </div>
                    <div>
                        <?php echo e($tickets->appends(request()->query())->links()); ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted">No tickets found matching your criteria.</p>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Ticket::class)): ?>
                    <a href="<?php echo e(route('tickets.create')); ?>" class="btn btn-primary">Create Your First Ticket</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/tickets/index.blade.php ENDPATH**/ ?>