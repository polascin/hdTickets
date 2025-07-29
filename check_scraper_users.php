<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $count = App\Models\User::where('role', 'scraper')->count();
    echo "Scraper users count: " . $count . "\n";
    
    // Also show some details about the scraper users
    $scraperUsers = App\Models\User::where('role', 'scraper')->take(5)->get(['id', 'name', 'email', 'role', 'created_at']);
    
    if ($scraperUsers->count() > 0) {
        echo "\nFirst few scraper users:\n";
        foreach ($scraperUsers as $user) {
            echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Created: {$user->created_at}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
