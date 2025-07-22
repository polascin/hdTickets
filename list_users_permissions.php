<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== USERS AND PERMISSIONS ===\n\n";

try {
    $users = User::all();
    
    if ($users->count() === 0) {
        echo "No users found in the database.\n";
        exit;
    }
    
    foreach ($users as $user) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "USER: {$user->username} ({$user->email})\n";
        echo "ID: {$user->id}\n";
        echo "Created: {$user->created_at}\n";
        echo "Phone: " . ($user->phone ?? 'Not set') . "\n";
        echo "Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
        
        // Check if this application uses Spatie Laravel Permission
        if (class_exists('Spatie\Permission\Models\Role')) {
            echo "\nROLES:\n";
            if ($user->roles->count() > 0) {
                foreach ($user->roles as $role) {
                    echo "  - {$role->name}\n";
                }
            } else {
                echo "  - No roles assigned\n";
            }
            
            echo "\nPERMISSIONS:\n";
            if ($user->permissions->count() > 0) {
                foreach ($user->permissions as $permission) {
                    echo "  - {$permission->name}\n";
                }
            } else {
                echo "  - No direct permissions assigned\n";
            }
            
            // Show all permissions (including via roles)
            $allPermissions = $user->getAllPermissions();
            if ($allPermissions->count() > 0) {
                echo "\nALL PERMISSIONS (including via roles):\n";
                foreach ($allPermissions as $permission) {
                    echo "  - {$permission->name}\n";
                }
            }
        } else {
            echo "\nNOTE: No permission system detected (Spatie Laravel Permission not installed)\n";
        }
        
        // Check for ticket alerts
        if ($user->ticketAlerts) {
            $alertCount = $user->ticketAlerts()->count();
            echo "\nTICKET ALERTS: {$alertCount} active alerts\n";
        }
        
        echo "\n";
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Total users: " . $users->count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
