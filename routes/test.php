<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// Test route to debug profile show
Route::get('/test-profile', function () {
    $user = \App\Models\User::first();
    if (!$user) {
        return 'No users found in database';
    }
    
    try {
        return view('profile.debug', ['user' => $user]);
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage() . '<br><br>Stack trace: <pre>' . $e->getTraceAsString() . '</pre>';
    }
})->name('test.profile');
