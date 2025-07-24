<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $totalUsers = App\Models\User::count();
    echo "Total users in database: " . $totalUsers . "\n\n";
    
    // Get counts by role
    $roleCounts = App\Models\User::select('role', App\Models\User::raw('count(*) as count'))
        ->groupBy('role')
        ->get();
    
    echo "Users by role:\n";
    foreach ($roleCounts as $role) {
        echo "- {$role->role}: {$role->count}\n";
    }
    
    echo "\nAll users:\n";
    $allUsers = App\Models\User::select(['id', 'name', 'email', 'role', 'created_at'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    foreach ($allUsers as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}, Created: {$user->created_at}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
