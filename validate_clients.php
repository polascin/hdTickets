<?php

// Simple validation script to check if all new client classes are properly defined

// Include required dependencies first
require_once 'app/Services/TicketApis/BaseApiClient.php';
require_once 'app/Services/TicketApis/BaseWebScrapingClient.php';

echo "Validating Platform Client Classes...\n\n";

// Define the classes to check
$clientClasses = [
    'App\\Services\\TicketApis\\ManchesterUnitedClient',
    'App\\Services\\TicketApis\\EventbriteClient', 
    'App\\Services\\TicketApis\\LiveNationClient',
    'App\\Services\\TicketApis\\AxsClient'
];

// Check if files exist and classes are defined
foreach ($clientClasses as $className) {
    $filePath = str_replace('\\', '/', $className) . '.php';
    $filePath = str_replace('App/', 'app/', $filePath);
    
    echo "Checking {$className}...\n";
    
    if (file_exists($filePath)) {
        echo "  ✓ File exists: {$filePath}\n";
        
        // Include the file to check syntax
        try {
            require_once $filePath;
            echo "  ✓ File includes successfully (no syntax errors)\n";
            
            if (class_exists($className)) {
                echo "  ✓ Class {$className} is defined\n";
                
                // Check if required abstract methods exist
                $requiredMethods = [
                    'extractSearchResults',
                    'extractEventFromNode', 
                    'extractPrices'
                ];
                
                foreach ($requiredMethods as $method) {
                    if (method_exists($className, $method)) {
                        echo "  ✓ Method {$method} exists\n";
                    } else {
                        echo "  ✗ Method {$method} missing\n";
                    }
                }
                
                // Check public interface methods
                $publicMethods = ['searchEvents', 'getEvent', 'getVenue', 'getBaseUrl'];
                foreach ($publicMethods as $method) {
                    if (method_exists($className, $method)) {
                        echo "  ✓ Public method {$method} exists\n";
                    } else {
                        echo "  ✗ Public method {$method} missing\n";
                    }
                }
                
            } else {
                echo "  ✗ Class {$className} not found\n";
            }
            
        } catch (ParseError $e) {
            echo "  ✗ Syntax error in file: " . $e->getMessage() . "\n";
        } catch (Exception $e) {
            echo "  ✗ Error loading file: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "  ✗ File not found: {$filePath}\n";
    }
    
    echo "\n";
}

echo "=== Validation Complete ===\n";
