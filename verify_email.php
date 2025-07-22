<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::where('email', 'test@example.com')->first();

if ($user) {
    $user->email_verified_at = now();
    $user->save();
    
    echo "✅ Email verified successfully for: {$user->email}\n";
    echo "Email verified at: {$user->email_verified_at}\n";
} else {
    echo "❌ User not found\n";
}

?>
