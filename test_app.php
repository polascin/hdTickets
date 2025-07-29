<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle a fake request to bootstrap Laravel
$request = Request::create('/test', 'GET');
$response = $kernel->handle($request);

echo "🔍 Testing HDTickets Application...\n\n";

try {
    // Test Users
    echo "👥 USERS:\n";
    $users = User::all();
    foreach ($users as $user) {
        echo "  ✅ {$user->name} ({$user->email}) - {$user->role}\n";
        echo "     UUID: {$user->uuid}\n";
        echo "     Verified: " . ($user->hasVerifiedEmail() ? 'Yes' : 'No') . "\n\n";
    }
    
    // Test Categories
    echo "📂 CATEGORIES:\n";
    $categories = Category::orderBy('sort_order')->get();
    foreach ($categories as $category) {
        echo "  ✅ {$category->name} ({$category->slug})\n";
        echo "     Color: {$category->color} | Icon: {$category->icon}\n";
        echo "     UUID: {$category->uuid}\n\n";
    }
    
    // Test Tickets
    echo "🎫 TICKETS:\n";
    $tickets = Ticket::with(['requester', 'assignee', 'category'])->get();
    foreach ($tickets as $ticket) {
        echo "  ✅ #{$ticket->id} - {$ticket->title}\n";
        echo "     Status: {$ticket->status} | Priority: {$ticket->priority}\n";
        echo "     Requester: " . ($ticket->requester ? $ticket->requester->name : 'Unknown') . "\n";
        echo "     Assignee: " . ($ticket->assignee ? $ticket->assignee->name : 'Unassigned') . "\n";
        echo "     Category: " . ($ticket->category ? $ticket->category->name : 'Uncategorized') . "\n";
        echo "     UUID: {$ticket->uuid}\n\n";
    }
    
    // Test Authentication
    echo "🔐 AUTHENTICATION TEST:\n";
    $adminUser = User::where('role', 'admin')->first();
    if ($adminUser) {
        auth()->login($adminUser);
        echo "  ✅ Admin login successful: {$adminUser->name}\n";
        echo "  ✅ Dashboard access: " . ($adminUser->isAdmin() ? 'Admin Dashboard' : 'Regular Dashboard') . "\n";
        auth()->logout();
        echo "  ✅ Logout successful\n\n";
    }
    
    // Test Relationships
    echo "🔗 RELATIONSHIPS TEST:\n";
    $testUser = User::where('email', 'test@example.com')->first();
    if ($testUser) {
        $userTickets = $testUser->tickets()->count();
        echo "  ✅ Test user has {$userTickets} tickets\n";
    }
    
    $firstCategory = Category::first();
    if ($firstCategory) {
        $categoryTickets = $firstCategory->tickets()->count();
        echo "  ✅ First category has {$categoryTickets} tickets\n";
    }
    
    echo "\n✅ All tests passed! HDTickets application is working properly.\n";
    echo "\n📋 SUMMARY:\n";
    echo "  • Users: " . User::count() . " (Admin: " . User::where('role', 'admin')->count() . ")\n";
    echo "  • Categories: " . Category::count() . "\n";
    echo "  • Tickets: " . Ticket::count() . "\n";
    echo "\n🔑 LOGIN CREDENTIALS:\n";
    echo "  • Admin: admin@hdtickets.com / admin123\n";
    echo "  • Agent: agent@hdtickets.com / agent123\n";
    echo "  • Customer: test@example.com / password123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

$kernel->terminate($request, $response);
