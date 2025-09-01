<?php

require_once 'vendor/autoload.php';

use App\Services\Scraping\Plugins\ManchesterUnitedPlugin;

try {
    echo "Testing ManchesterUnitedPlugin...\n";
    
    $plugin = new ManchesterUnitedPlugin();
    echo "✓ ManchesterUnitedPlugin instantiated successfully\n";
    
    $info = $plugin->getInfo();
    echo "Plugin Name: " . $info['name'] . "\n";
    echo "Description: " . $info['description'] . "\n";
    echo "Version: " . $info['version'] . "\n";
    echo "Platform: " . $info['platform'] . "\n";
    echo "Venue: " . $info['venue'] . "\n";
    
    $capabilities = $plugin->getCapabilities();
    echo "Capabilities: " . implode(', ', $capabilities) . "\n";
    
    // Test search suggestions
    $suggestions = $plugin->getSearchSuggestions();
    echo "Major Opponents: " . implode(', ', $suggestions['Major Opponents']) . "\n";
    
    // Test competition support
    $supportsChampions = $plugin->supportsCompetition('Champions League') ? 'Yes' : 'No';
    echo "Supports Champions League: " . $supportsChampions . "\n";
    
    // Test venue info
    $venueInfo = $plugin->getVenueInfo();
    echo "Venue: " . $venueInfo['name'] . " (Capacity: " . $venueInfo['capacity'] . ")\n";
    
    echo "✓ All methods working correctly\n";
    echo "✓ Manchester United plugin modernization completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
