<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    
    if ($user->isAdmin()) {
        return view('dashboard.admin');
    } elseif ($user->isAgent()) {
        return view('dashboard.agent');
    } else {
        return view('dashboard.customer');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Role-specific dashboard routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard.admin');
    })->name('admin.dashboard');
});

Route::middleware(['auth', 'verified', 'agent'])->group(function () {
    Route::get('/agent/dashboard', function () {
        return view('dashboard.agent');
    })->name('agent.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/customer/dashboard', function () {
        return view('dashboard.customer');
    })->name('customer.dashboard');
});

// Admin user management routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('admin/users', App\Http\Controllers\Admin\UserManagementController::class)->names('admin.users');
    Route::patch('admin/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');
});

// Ticket Sources routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ticket-sources', App\Http\Controllers\TicketSourceController::class);
    Route::patch('ticket-sources/{ticket_source}/toggle', [App\Http\Controllers\TicketSourceController::class, 'toggle'])->name('ticket-sources.toggle');
});

// Ticket API Integration routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('ticket-api')->group(function () {
    Route::get('/', [App\Http\Controllers\TicketApiController::class, 'index'])->name('ticket-api.index');
    Route::post('/search', [App\Http\Controllers\TicketApiController::class, 'search'])->name('ticket-api.search');
    Route::post('/import', [App\Http\Controllers\TicketApiController::class, 'importEvents'])->name('ticket-api.import');
    Route::get('/test-connections', [App\Http\Controllers\TicketApiController::class, 'testConnections'])->name('ticket-api.test');
    Route::get('/event/{platform}/{eventId}', [App\Http\Controllers\TicketApiController::class, 'getEvent'])->name('ticket-api.event');
});

// API routes for ticket sources
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('ticket-sources', [App\Http\Controllers\TicketSourceController::class, 'apiIndex']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
