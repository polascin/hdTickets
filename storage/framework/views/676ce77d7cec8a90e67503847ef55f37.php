

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Scraped Tickets</h1>

    <div class="card mb-4">
        <div class="card-header">
            <form method="GET" action="<?php echo e(route('tickets.scraping.index')); ?>">
                <div class="row">
                    <div class="col-md-4">
                        <label for="platform" class="form-label">Platform</label>
                        <input type="text" class="form-control" name="platform" value="<?php echo e(request('platform')); ?>" placeholder="Platform">
                    </div>
                    <div class="col-md-4">
                        <label for="keywords" class="form-label">Keywords</label>
                        <input type="text" class="form-control" name="keywords" value="<?php echo e(request('keywords')); ?>" placeholder="Keywords">
                    </div>
                    <div class="col-md-4">
                        <label for="max_price" class="form-label">Max Price</label>
                        <input type="number" class="form-control" name="max_price" value="<?php echo e(request('max_price')); ?>" placeholder="Max Price">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="<?php echo e(route('tickets.scraping.index')); ?>" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if($tickets->count()): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($ticket->id); ?></td>
                        <td><?php echo e($ticket->title); ?></td>
                        <td><?php echo e($ticket->event_date ? $ticket->event_date->format('M d, Y') : 'TBD'); ?></td>
                        <td>
                            <?php if($ticket->min_price && $ticket->max_price): ?>
                                <?php echo e($ticket->currency); ?> <?php echo e(number_format($ticket->min_price, 2)); ?> - <?php echo e(number_format($ticket->max_price, 2)); ?>

                            <?php elseif($ticket->max_price): ?>
                                <?php echo e($ticket->currency); ?> <?php echo e(number_format($ticket->max_price, 2)); ?>

                            <?php elseif($ticket->min_price): ?>
                                <?php echo e($ticket->currency); ?> <?php echo e(number_format($ticket->min_price, 2)); ?>

                            <?php else: ?>
                                Price on request
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($ticket->is_available): ?>
                                <span class="badge bg-success">Available</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Sold Out</span>
                            <?php endif; ?>
                            <?php if($ticket->is_high_demand): ?>
                                <span class="badge bg-warning">High Demand</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo e(route('tickets.scraping.show', $ticket)); ?>" class="btn btn-outline-primary btn-sm">View</a>
                            <?php if($ticket->ticket_url): ?>
                                <a href="<?php echo e($ticket->ticket_url); ?>" target="_blank" class="btn btn-success btn-sm">Buy</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <?php echo e($tickets->appends(request()->query())->links()); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-warning">No scraped tickets found.</div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/tickets/scraping/index.blade.php ENDPATH**/ ?>