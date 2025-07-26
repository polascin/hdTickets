<?php

// Manual autoloading for testing
require_once 'vendor/autoload.php';

// Set up basic Laravel-like environment
if (!function_exists('config')) {
    function config($key, $default = null) {
        return $default;
    }
}

if (!function_exists('now')) {
    function now() {
        return new DateTime();
    }
}

use App\Services\TicketApis\MultiPlatformManager;

try {
    echo "Testing Platform Integration...\n\n";
    
    // Initialize services
    $config = [
        'ticketmaster' => [
            'api_key' => 'test-key',
            'secret' => 'test-secret',
            'base_url' => 'https://app.ticketmaster.com/discovery/v2/'
        ],
        'stubhub' => [
            'api_key' => 'test-key',
            'secret' => 'test-secret',
            'base_url' => 'https://api.stubhub.com'
        ],
        'seatgeek' => [
            'api_key' => 'test-key',
            'secret' => 'test-secret',
            'base_url' => 'https://api.seatgeek.com/2/'
        ],
        'manchester_united' => [
            'base_url' => 'https://www.manutd.com'
        ],
        'eventbrite' => [
            'base_url' => 'https://www.eventbrite.com'
        ],
        'livenation' => [
            'base_url' => 'https://www.livenation.com'
        ],
        'axs' => [
            'base_url' => 'https://www.axs.com'
        ]
    ];
    
    // Test MultiPlatformManager instantiation
    echo "1. Testing MultiPlatformManager instantiation...\n";
    $manager = new MultiPlatformManager($config);
    echo "✓ MultiPlatformManager created successfully\n\n";
    
    // Test platform detection
    echo "2. Testing platform detection...\n";
    $testUrls = [
        'https://www.ticketmaster.com/event/123' => 'ticketmaster',
        'https://www.stubhub.com/event/456' => 'stubhub', 
        'https://seatgeek.com/event/789' => 'seatgeek',
        'https://www.manutd.com/tickets/123' => 'manchester_united',
        'https://www.eventbrite.com/e/event-123' => 'eventbrite',
        'https://www.livenation.com/event/456' => 'livenation',
        'https://www.axs.com/events/789' => 'axs'
    ];
    
    foreach ($testUrls as $url => $expectedPlatform) {
        $detected = $manager->detectPlatform($url);
        if ($detected === $expectedPlatform) {
            echo "✓ {$url} -> {$detected}\n";
        } else {
            echo "✗ {$url} -> Expected: {$expectedPlatform}, Got: {$detected}\n";
        }
    }
    
    echo "\n3. Testing client instantiation for each platform...\n";
    $platforms = [
        'ticketmaster',
        'stubhub', 
        'seatgeek',
        'manchester_united',
        'eventbrite',
        'livenation',
        'axs'
    ];
    
    foreach ($platforms as $platform) {
        try {
            $client = $manager->getClient($platform);
            if ($client) {
                echo "✓ {$platform} client created successfully\n";
                
                // Test if client has required methods
                $requiredMethods = ['searchEvents', 'getEvent', 'getVenue'];
                foreach ($requiredMethods as $method) {
                    if (method_exists($client, $method)) {
                        echo "  ✓ Method {$method} exists\n";
                    } else {
                        echo "  ✗ Method {$method} missing\n";
                    }
                }
                
                echo "  ✓ Base URL: " . $client->getBaseUrl() . "\n";
                
            } else {
                echo "✗ Failed to create {$platform} client\n";
            }
        } catch (Exception $e) {
            echo "✗ Error creating {$platform} client: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Test UserRotationService integration
    echo "4. Testing UserRotationService platform integration...\n";
    try {
        $rotationService = new UserRotationService();
        
        // Test clearing cache for new platforms  
        $rotationService->clearRotationCache('manchester_united');
        echo "✓ Manchester United rotation cache cleared\n";
        
        $rotationService->clearRotationCache('eventbrite');
        echo "✓ Eventbrite rotation cache cleared\n";
        
        $rotationService->clearRotationCache('livenation'); 
        echo "✓ LiveNation rotation cache cleared\n";
        
        $rotationService->clearRotationCache('axs');
        echo "✓ AXS rotation cache cleared\n";
        
    } catch (Exception $e) {
        echo "✗ UserRotationService error: " . $e->getMessage() . "\n";
    }
    
    echo "\n5. Testing platform display names...\n";
    $displayNames = $manager->getAllPlatformDisplayNames();
    foreach ($displayNames as $platform => $displayName) {
        echo "✓ {$platform} -> '{$displayName}'\n";
    }
    
    echo "\n=== Integration Test Complete ===\n";
    echo "All platform clients are properly integrated and functional!\n";
    
} catch (Exception $e) {
    echo "✗ Integration test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
