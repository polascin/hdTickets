<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;

echo "=== HDTickets System - CRUD Operations Test ===\n\n";

try {
    // Test READ operations
    echo "📊 Testing READ operations:\n";
    $userCount = User::count();
    $categoryCount = Category::count();
    $ticketCount = Ticket::count();
    
    echo "  • Users: $userCount\n";
    echo "  • Categories: $categoryCount\n";
    echo "  • Tickets: $ticketCount\n\n";
    
    // Test CREATE operation
    echo "✏️ Testing CREATE operation:\n";
    $testCategory = Category::create([
        'name' => 'Test Category CRUD',
        'slug' => 'test-category-crud',
        'description' => 'Test category for CRUD validation',
        'is_active' => true,
        'sort_order' => 999
    ]);
    echo "  • Created test category with ID: {$testCategory->id}\n";
    
    // Test UPDATE operation
    echo "🔄 Testing UPDATE operation:\n";
    $testCategory->update([
        'description' => 'Updated test category for CRUD validation'
    ]);
    echo "  • Updated category description successfully\n";
    
    // Test another READ to verify update
    $updatedCategory = Category::find($testCategory->id);
    echo "  • Verified update: {$updatedCategory->description}\n";
    
    // Test DELETE operation
    echo "🗑️ Testing DELETE operation:\n";
    $testCategory->delete();
    echo "  • Deleted test category successfully\n";
    
    // Verify deletion
    $deletedCategory = Category::find($testCategory->id);
    if (!$deletedCategory) {
        echo "  • Verified deletion: Category no longer exists\n";
    }
    
    echo "\n✅ All CRUD operations completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ CRUD test failed: " . $e->getMessage() . "\n";
    exit(1);
}
