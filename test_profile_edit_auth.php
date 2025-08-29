<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Create a test user or use existing user
    $user = User::first();
    
    if (!$user) {
        echo "No users found in database\n";
        exit;
    }
    
    echo "Testing with user: " . $user->email . "\n";
    
    // Create a test request to profile edit
    $request = Request::create('/profile/edit', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Set up session to simulate logged-in user
    $session = app('session.store');
    $session->put('_token', 'test-token');
    $session->put('login_web_' . sha1('web'), $user->id);
    $request->setSession($session);
    
    // Manually authenticate the user
    Auth::login($user);
    
    $response = $kernel->handle($request);
    
    echo "HTTP Status: " . $response->getStatusCode() . "\n";
    echo "Content Type: " . $response->headers->get('content-type') . "\n";
    
    if ($response->getStatusCode() == 302) {
        echo "Redirect Location: " . $response->headers->get('location') . "\n";
    }
    
    if ($response->getStatusCode() == 200) {
        echo "Page rendered successfully\n";
        // Check if specific content is present
        $content = $response->getContent();
        if (strpos($content, 'Profile Information') !== false) {
            echo "Profile form found\n";
        }
        if (strpos($content, 'name="name"') !== false) {
            echo "Name field found\n";
        }
    }
    
    if ($response->getStatusCode() >= 400) {
        echo "Error occurred:\n";
        echo substr($response->getContent(), 0, 1000) . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response ?? null);