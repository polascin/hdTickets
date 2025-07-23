<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\ScrapingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group.
|
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats.json', [DashboardController::class, 'getStats']);
    Route::get('/chart/status.json', [DashboardController::class, 'getTicketStatusChart']);
    Route::get('/chart/priority.json', [DashboardController::class, 'getTicketPriorityChart']);
    Route::get('/chart/monthly-trend.json', [DashboardController::class, 'getMonthlyTrend']);
    Route::get('/chart/role-distribution.json', [DashboardController::class, 'getRoleDistributionChart']);
    Route::get('/activity/recent.json', [DashboardController::class, 'getRecentActivity']);

    // User Management Routes (Admin Only)
    Route::middleware('admin:manage_users')->group(function () {
        Route::resource('users', UserManagementController::class)->names('users');
        Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/bulk-action', [UserManagementController::class, 'bulkAction'])->name('users.bulk-action');
        Route::post('users/{user}/impersonate', [UserManagementController::class, 'impersonate'])->name('users.impersonate');
        Route::post('users/{user}/send-verification', [UserManagementController::class, 'sendVerification'])->name('users.send-verification');
        Route::patch('users/{user}/inline-update', [UserManagementController::class, 'inlineUpdate'])->name('users.inline-update');
        
        // User Roles Management
        Route::get('users/roles', [UserManagementController::class, 'roles'])->name('users.roles');
        Route::patch('users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
        Route::post('users/bulk-role-assignment', [UserManagementController::class, 'bulkRoleAssignment'])->name('users.bulk-role-assignment');
        
        Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
    });

    // Reports and Export/Import Routes
    Route::prefix('reports')->name('reports.')->middleware('admin:access_reports')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/users/export', [ReportsController::class, 'exportUsers'])->name('users.export');
        Route::get('/tickets/export', [ReportsController::class, 'exportScrapedTickets'])->name('tickets.export');
        Route::get('/audit/export', [ReportsController::class, 'exportAuditTrail'])->name('audit.export');
        Route::post('/users/import', [ReportsController::class, 'importUsers'])->name('users.import');
        Route::get('/pdf/users', [ReportsController::class, 'generateUsersPDF'])->name('pdf.users');
        Route::get('/pdf/tickets', [ReportsController::class, 'generateTicketsPDF'])->name('pdf.tickets');
        Route::get('/pdf/audit', [ReportsController::class, 'generateAuditPDF'])->name('pdf.audit');
    });

    // Activity Log Management Routes (Admin Only)
    Route::prefix('activity-logs')->name('activity-logs.')->middleware('admin:manage_system')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
        Route::get('/api/security-activities', [ActivityLogController::class, 'getSecurityActivities'])->name('security-activities');
        Route::get('/api/user-summary/{user}', [ActivityLogController::class, 'getUserActivitySummary'])->name('user-summary');
        Route::post('/api/bulk-token', [ActivityLogController::class, 'generateBulkToken'])->name('bulk-token');
        Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
        Route::delete('/cleanup', [ActivityLogController::class, 'cleanup'])->name('cleanup');
    });

    // Category Management Routes
    Route::resource('categories', CategoryManagementController::class)->names('categories');
    Route::patch('categories/{category}/toggle-status', [CategoryManagementController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::post('categories/reorder', [CategoryManagementController::class, 'reorder'])->name('categories.reorder');

    // System Management Routes
    Route::prefix('system')->name('system.')->middleware('admin:manage_system')->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::get('health', [SystemController::class, 'getHealth'])->name('health');
        Route::get('configuration', [SystemController::class, 'getConfiguration'])->name('configuration');
        Route::post('configuration', [SystemController::class, 'updateConfiguration'])->name('configuration.update');
        Route::get('logs', [SystemController::class, 'getLogs'])->name('logs');
        Route::post('cache/clear', [SystemController::class, 'clearCache'])->name('cache.clear');
        Route::post('maintenance', [SystemController::class, 'runMaintenance'])->name('maintenance');
        Route::get('disk-usage', [SystemController::class, 'getDiskUsage'])->name('disk-usage');
        Route::get('database-info', [SystemController::class, 'getDatabaseInfo'])->name('database-info');
    });

    // Scraping Management Routes
    Route::prefix('scraping')->name('scraping.')->middleware('admin:access_scraping')->group(function () {
        Route::get('/', [ScrapingController::class, 'index'])->name('index');
        Route::get('stats', [ScrapingController::class, 'getStats'])->name('stats');
        Route::get('platforms', [ScrapingController::class, 'getPlatformStats'])->name('platforms');
        Route::get('operations', [ScrapingController::class, 'getRecentOperations'])->name('operations');
        Route::get('user-rotation', [ScrapingController::class, 'getUserRotation'])->name('user-rotation');
        Route::post('rotation-test', [ScrapingController::class, 'testRotation'])->name('rotation-test');
        Route::get('configuration', [ScrapingController::class, 'getConfig'])->name('configuration');
        Route::post('configuration', [ScrapingController::class, 'updateConfig'])->name('configuration.update');
        Route::get('performance', [ScrapingController::class, 'getPerformanceMetrics'])->name('performance');
    });

    // Activity and Health APIs
    Route::get('activities/recent', function () {
        $activities = [
            [
                'id' => 1,
                'type' => 'user',
                'message' => 'New user registered: john@example.com',
                'status' => 'completed',
                'timestamp' => now()->subMinutes(5)->toISOString()
            ],
            [
                'id' => 2,
                'type' => 'ticket',
                'message' => 'Ticket #123 was assigned to Agent Smith',
                'status' => 'completed',
                'timestamp' => now()->subMinutes(15)->toISOString()
            ],
            [
                'id' => 3,
                'type' => 'config',
                'message' => 'System configuration updated',
                'status' => 'completed',
                'timestamp' => now()->subHour()->toISOString()
            ]
        ];
        return response()->json($activities);
    })->name('activities.recent');
});

// Stop impersonation route (global access)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/admin/users/stop-impersonating', [UserManagementController::class, 'stopImpersonating'])->name('admin.users.stop-impersonating');
});
