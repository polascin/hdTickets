<?php

$files_to_fix = [
    'app/Http/Controllers/Admin/ReportsController.php' => [
        'index' => ': \\Illuminate\\Contracts\\View\\View',
        'ticketVolume' => ': \\Illuminate\\Contracts\\View\\View|\\Illuminate\\Http\\JsonResponse',
        'agentPerformance' => ': \\Illuminate\\Contracts\\View\\View|\\Illuminate\\Http\\JsonResponse',
        'categoryAnalysis' => ': \\Illuminate\\Contracts\\View\\View|\\Illuminate\\Http\\JsonResponse',
        'responseTime' => ': \\Illuminate\\Contracts\\View\\View|\\Illuminate\\Http\\JsonResponse',
        'export' => ': \\Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'exportUsers' => ': \\Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'exportScrapedTickets' => ': \\Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'exportAuditTrail' => ': \\Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'importUsers' => ': \\Illuminate\\Http\\RedirectResponse',
        'generateUsersPDF' => ': \\Illuminate\\Http\\Response',
        'generateTicketsPDF' => ': \\Illuminate\\Http\\Response',
        'generateAuditPDF' => ': \\Illuminate\\Http\\Response',
        'ticketAvailabilityTrends' => ': \\Illuminate\\Contracts\\View\\View',
        'priceFluctuationAnalysis' => ': \\Illuminate\\Contracts\\View\\View',
        'platformPerformanceComparison' => ': \\Illuminate\\Contracts\\View\\View',
        'userEngagementMetrics' => ': \\Illuminate\\Contracts\\View\\View',
    ],
    'app/Http/Controllers/Admin/ScrapingController.php' => [
        'getRecentOperations' => ': \\Illuminate\\Http\\JsonResponse',
        'testRotation' => ': \\Illuminate\\Http\\JsonResponse',
        'updateConfig' => ': \\Illuminate\\Http\\JsonResponse',
        'testAntiDetection' => ': \\Illuminate\\Http\\JsonResponse',
        'testHighDemand' => ': \\Illuminate\\Http\\JsonResponse',
        'getAdvancedLogs' => ': \\Illuminate\\Http\\JsonResponse',
        'configureAntiDetection' => ': \\Illuminate\\Http\\JsonResponse',
        'configureHighDemand' => ': \\Illuminate\\Http\\JsonResponse',
        'getScrapingStats' => ': \\Illuminate\\Http\\JsonResponse',
    ],
];

foreach ($files_to_fix as $file => $methods) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $modified = false;
    
    foreach ($methods as $method => $returnType) {
        // Check if method already has return type
        if (strpos($content, "public function {$method}(") !== false || 
            strpos($content, "protected function {$method}(") !== false ||
            strpos($content, "private function {$method}(") !== false) {
            
            // Pattern to find method without return type
            $pattern = '/((?:public|protected|private)\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\))\s*(?!\s*:)(\s*\{)/';
            
            if (preg_match($pattern, $content)) {
                $replacement = '$1' . $returnType . '$2';
                $content = preg_replace($pattern, $replacement, $content);
                $modified = true;
                echo "Added return type for method: {$method} in {$file}\n";
            }
        }
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "Updated file: $file\n\n";
    }
}

echo "Done!\n";
