<?php

/**
 * Fix uninitialized class properties by adding default values
 * or proper constructor initialization
 */

$files = [
    'app/Services/Core/PurchaseAutomationService.php',
    'app/Services/Core/ScrapingService.php', 
    'app/Services/NotificationService.php',
    'app/Services/Scraping/BaseScraperPlugin.php',
    'tests/Integration/Api/TicketApiTest.php',
    'tests/TestCase.php',
    'tests/Unit/Services/NotificationServiceTest.php',
    'tests/Unit/Services/ScrapingServiceTest.php',
];

$filesFixed = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern 1: Add default null values for properties that can be null
    $patterns = [
        // Service class properties that can default to null
        '/protected \$([a-zA-Z_][a-zA-Z0-9_]*);(\s*\/\*\*|\s*\/\/|\s*protected|\s*private|\s*public|\s*\})/' => 'protected $$$1 = null;$2',
        '/private \$([a-zA-Z_][a-zA-Z0-9_]*);(\s*\/\*\*|\s*\/\/|\s*protected|\s*private|\s*public|\s*\})/' => 'private $$$1 = null;$2',
        
        // Properties that should have empty array defaults
        '/protected \$([a-zA-Z_]*[Ll]ist|[a-zA-Z_]*[Aa]rray|[a-zA-Z_]*[Ii]tems);/' => 'protected $$$1 = [];',
        '/private \$([a-zA-Z_]*[Ll]ist|[a-zA-Z_]*[Aa]rray|[a-zA-Z_]*[Ii]tems);/' => 'private $$$1 = [];',
        
        // Properties that should have empty string defaults
        '/protected \$([a-zA-Z_]*[Nn]ame|[a-zA-Z_]*[Dd]escription|[a-zA-Z_]*[Uu]rl);/' => 'protected $$$1 = \'\';',
        '/private \$([a-zA-Z_]*[Nn]ame|[a-zA-Z_]*[Dd]escription|[a-zA-Z_]*[Uu]rl);/' => 'private $$$1 = \'\';',
        
        // Boolean properties default to false
        '/protected \$([a-zA-Z_]*[Ee]nabled|[a-zA-Z_]*[Aa]ctive|[a-zA-Z_]*[Vv]alid);/' => 'protected $$$1 = false;',
        '/private \$([a-zA-Z_]*[Ee]nabled|[a-zA-Z_]*[Aa]ctive|[a-zA-Z_]*[Vv]alid);/' => 'private $$$1 = false;',
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Specific fixes for known problem properties
    $specificFixes = [
        // PurchaseAutomationService
        'protected $decisionChain;' => 'protected $decisionChain = null;',
        'protected $strategyFactory;' => 'protected $strategyFactory = null;',
        
        // ScrapingService  
        'protected $adapterFactory;' => 'protected $adapterFactory = null;',
        
        // NotificationService
        'protected $channelFactory;' => 'protected $channelFactory = null;',
        
        // BaseScraperPlugin
        'protected $antiDetection;' => 'protected $antiDetection = null;',
        'protected $highDemandScraper;' => 'protected $highDemandScraper = null;',
        'protected $pluginName;' => 'protected $pluginName = \'\';',
        'protected $platform;' => 'protected $platform = \'\';',
        'protected $description;' => 'protected $description = \'\';',
        'protected $baseUrl;' => 'protected $baseUrl = \'\';',
        'protected $venue;' => 'protected $venue = \'\';',
        
        // Test classes
        'protected $user;' => 'protected $user = null;',
        'protected $admin;' => 'protected $admin = null;',
        'protected $testDataFactory;' => 'protected $testDataFactory = null;',
        'protected $notificationService;' => 'protected $notificationService = null;',
        'protected $scrapingService;' => 'protected $scrapingService = null;',
    ];
    
    foreach ($specificFixes as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $filesFixed++;
        echo "Fixed uninitialized properties in: " . basename($file) . "\n";
    } else {
        echo "No changes needed: " . basename($file) . "\n";
    }
}

echo "\nUninitialized Properties Fix Summary:\n";
echo "Files processed: " . count($files) . "\n";
echo "Files fixed: $filesFixed\n";

// Now let's also fix some specific cases that need constructor initialization
echo "\nApplying constructor initialization fixes...\n";

// Fix BaseScraperPlugin constructor
$baseScraperFile = 'app/Services/Scraping/BaseScraperPlugin.php';
if (file_exists($baseScraperFile)) {
    $content = file_get_contents($baseScraperFile);
    $originalContent = $content;
    
    // Check if it has a constructor, if not add one
    if (!strpos($content, '__construct')) {
        // Add constructor after the property declarations
        $content = preg_replace(
            '/(protected \$venue[^;]*;)(\s*)(\/\*\*|\s*public|\s*protected|\s*private)/',
            '$1$2' . "\n\n    public function __construct()\n    {\n        // Initialize properties in subclasses\n    }\n\n$2$3",
            $content
        );
        
        if ($content !== $originalContent) {
            file_put_contents($baseScraperFile, $content);
            echo "Added constructor to BaseScraperPlugin.php\n";
        }
    }
}

echo "\nProperty initialization fixes completed!\n";
