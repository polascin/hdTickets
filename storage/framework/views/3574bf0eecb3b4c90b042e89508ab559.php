

<?php $__env->startSection('title', 'Category Analysis Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.reports.index')); ?>">Reports</a></li>
                    <li class="breadcrumb-item active">Category Analysis</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Category Analysis Report</h1>
        </div>
        
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.reports.export', ['type' => 'category_analysis', 'format' => 'csv'])); ?>" class="btn btn-outline-primary">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <a href="<?php echo e(route('admin.reports.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="<?php echo e(request('start_date', now()->subMonth()->format('Y-m-d'))); ?>">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="<?php echo e(request('end_date', now()->format('Y-m-d'))); ?>">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Overview -->
    <div class="row mb-4">
        <?php if(count($categoryData) > 0): ?>
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Active Categories
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(count($categoryData)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Total Tickets
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo e(number_format(collect($categoryData)->sum('total_tickets'))); ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Avg Resolution Rate
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo e(round(collect($categoryData)->avg('resolution_rate'), 1)); ?>%
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Overdue Tickets
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php echo e(number_format(collect($categoryData)->sum('overdue_tickets'))); ?>

                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Ticket Distribution by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryDistributionChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Resolution Rate by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="resolutionRateChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Category Analysis -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Category Performance Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Tickets</th>
                            <th>Resolved Tickets</th>
                            <th>Overdue Tickets</th>
                            <th>Resolution Rate</th>
                            <th>Avg Resolution Time</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $categoryData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" 
                                         style="width: 35px; height: 35px; font-size: 12px;">
                                        <?php echo e(strtoupper(substr($category['name'], 0, 2))); ?>

                                    </div>
                                    <strong><?php echo e($category['name']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo e(number_format($category['total_tickets'])); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-success"><?php echo e(number_format($category['resolved_tickets'])); ?></span>
                            </td>
                            <td>
                                <?php if($category['overdue_tickets'] > 0): ?>
                                    <span class="badge bg-danger"><?php echo e(number_format($category['overdue_tickets'])); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?php echo e($category['resolution_rate']); ?>%</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar <?php echo e($category['resolution_rate'] >= 80 ? 'bg-success' : ($category['resolution_rate'] >= 60 ? 'bg-warning' : 'bg-danger')); ?>" 
                                             style="width: <?php echo e($category['resolution_rate']); ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($category['avg_resolution_time'] > 0): ?>
                                    <span class="text-muted"><?php echo e($category['avg_resolution_time']); ?>h</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($category['resolution_rate'] >= 80 && $category['overdue_tickets'] == 0): ?>
                                    <span class="badge bg-success">Excellent</span>
                                <?php elseif($category['resolution_rate'] >= 60 && $category['overdue_tickets'] <= 5): ?>
                                    <span class="badge bg-warning">Good</span>
                                <?php elseif($category['resolution_rate'] >= 40): ?>
                                    <span class="badge bg-info">Average</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Needs Attention</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No category data available for the selected period</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Category Distribution Chart (Doughnut)
const ctx1 = document.getElementById('categoryDistributionChart').getContext('2d');
const categoryColors = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
];

const distributionChart = new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(collect($categoryData)->pluck('name'), 15, 512) ?>,
        datasets: [{
            data: <?php echo json_encode(collect($categoryData)->pluck('total_tickets'), 15, 512) ?>,
            backgroundColor: categoryColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

// Resolution Rate Chart (Bar)
const ctx2 = document.getElementById('resolutionRateChart').getContext('2d');
const resolutionChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(collect($categoryData)->pluck('name'), 15, 512) ?>,
        datasets: [{
            label: 'Resolution Rate (%)',
            data: <?php echo json_encode(collect($categoryData)->pluck('resolution_rate'), 15, 512) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Resolution Rate (%)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Category'
                }
            }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\admin\reports\category-analysis.blade.php ENDPATH**/ ?>