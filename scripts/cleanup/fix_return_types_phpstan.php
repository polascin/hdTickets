<?php

/**
 * Script to fix missing return types based on PHPStan analysis
 */

function fixReturnTypes($filePath, $fixes) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return false;
    }
    
    $content = file_get_contents($filePath);
    $modified = false;
    
    foreach ($fixes as $method => $returnType) {
        // Pattern to match method declarations without return types
        $pattern = '/public\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*(?!:\s*\w)/';
        
        if (preg_match($pattern, $content)) {
            // Find the method and add return type
            $content = preg_replace_callback(
                '/public\s+function\s+' . preg_quote($method, '/') . '\s*(\([^)]*\))\s*(?!:\s*\w)(\s*{)/',
                function($matches) use ($returnType) {
                    return 'public function ' . $method . $matches[1] . ': ' . $returnType . $matches[2];
                },
                $content
            );
            $modified = true;
            echo "Fixed return type for method: {$method} -> {$returnType}\n";
        }
        
        // Also handle private/protected methods
        $pattern = '/(?:private|protected)\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*(?!:\s*\w)/';
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace_callback(
                '/(?:private|protected)\s+function\s+' . preg_quote($method, '/') . '\s*(\([^)]*\))\s*(?!:\s*\w)(\s*{)/',
                function($matches) use ($returnType, $method) {
                    $visibility = strpos($matches[0], 'private') !== false ? 'private' : 'protected';
                    return $visibility . ' function ' . $method . $matches[1] . ': ' . $returnType . $matches[2];
                },
                $content
            );
            $modified = true;
            echo "Fixed return type for method: {$method} -> {$returnType}\n";
        }
    }
    
    if ($modified) {
        file_put_contents($filePath, $content);
        echo "Updated file: $filePath\n";
        return true;
    }
    
    return false;
}

// Define fixes for specific files
$controllerFixes = [
    'app/Http/Controllers/Admin/DashboardController.php' => [
        'getStatusColor' => 'string',
        'getPriorityColor' => 'string',
        'calculateSystemHealth' => 'float',
        'checkDatabaseHealth' => 'bool',
        'getRoleColor' => 'string',
        'getTotalScrapedToday' => 'int',
        'getActiveScrapers' => 'int',
        'getScrapingSuccessRate' => 'float',
        'getPlatformPerformance' => 'array',
        'getRecentScrapingActivity' => 'array',
        'getPriceTrends' => 'array',
        'getAlertTriggers' => 'array',
        'getDayLogins' => 'int',
        'getDayTicketViews' => 'int',
        'getDayPurchases' => 'int',
        'getDayAlertsCreated' => 'int',
        'getDailyRevenue' => 'float',
        'getMonthlyRevenue' => 'float',
        'getAverageTicketPrice' => 'float',
        'getPriceRangeDistribution' => 'array',
        'getTopSellingEvents' => 'array',
        'getRevenueByPlatform' => 'array',
        'getProfitMargins' => 'array'
    ],
    'app/Http/Controllers/Admin/ReportsController.php' => [
        'ticketVolume' => 'Illuminate\\Contracts\\View\\View',
        'agentPerformance' => 'Illuminate\\Contracts\\View\\View',
        'categoryAnalysis' => 'Illuminate\\Contracts\\View\\View',
        'responseTime' => 'Illuminate\\Contracts\\View\\View',
        'export' => 'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'exportUsers' => 'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'exportScrapedTickets' => 'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'exportAuditTrail' => 'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'importUsers' => 'Illuminate\\Http\\RedirectResponse',
        'generateUsersPDF' => 'Illuminate\\Http\\Response',
        'generateTicketsPDF' => 'Illuminate\\Http\\Response',
        'generateAuditPDF' => 'Illuminate\\Http\\Response',
        'ticketAvailabilityTrends' => 'Illuminate\\Contracts\\View\\View',
        'priceFluctuationAnalysis' => 'Illuminate\\Contracts\\View\\View',
        'platformPerformanceComparison' => 'Illuminate\\Contracts\\View\\View',
        'userEngagementMetrics' => 'Illuminate\\Contracts\\View\\View',
        'getAverageResponseTime' => 'float',
        'getAverageResolutionTime' => 'float',
        'getTopAgents' => 'array',
        'getAgentWorkload' => 'array',
        'getWeeklyTrend' => 'array',
        'getWeeklyTrendForScrapedTickets' => 'array',
        'getMonthlyTrend' => 'array',
        'calculateMedian' => 'float',
        'getAgentAverageResolutionTime' => 'array',
        'getAgentAverageResponseTime' => 'array',
        'getCategoryAverageResolutionTime' => 'array',
        'exportTickets' => 'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'getPlatformPerformanceMetrics' => 'array',
        'getUserEngagementData' => 'array'
    ],
    'app/Http/Controllers/Admin/ScrapingController.php' => [
        'getRecentOperations' => 'Illuminate\\Http\\JsonResponse',
        'testRotation' => 'Illuminate\\Http\\JsonResponse',
        'updateConfig' => 'Illuminate\\Http\\JsonResponse',
        'testAntiDetection' => 'Illuminate\\Http\\JsonResponse',
        'testHighDemand' => 'Illuminate\\Http\\JsonResponse',
        'getAdvancedLogs' => 'Illuminate\\Http\\JsonResponse',
        'configureAntiDetection' => 'Illuminate\\Http\\JsonResponse',
        'configureHighDemand' => 'Illuminate\\Http\\JsonResponse',
        'getScrapingStats' => 'Illuminate\\Http\\JsonResponse',
        'getAdvancedStats' => 'array',
        'getProtectedSuccessRate' => 'float'
    ],
    'app/Http/Controllers/Admin/SystemController.php' => [
        'updateConfiguration' => 'Illuminate\\Http\\JsonResponse',
        'getLogs' => 'Illuminate\\Http\\JsonResponse',
        'clearCache' => 'Illuminate\\Http\\JsonResponse',
        'runMaintenance' => 'Illuminate\\Http\\JsonResponse',
        'getSystemHealth' => 'array',
        'getSystemConfig' => 'array',
        'getServiceStatus' => 'array',
        'measureDatabaseResponseTime' => 'float'
    ],
    'app/Http/Controllers/Admin/TicketManagementController.php' => [
        'index' => 'Illuminate\\Contracts\\View\\View',
        'assign' => 'Illuminate\\Http\\JsonResponse',
        'bulkAssign' => 'Illuminate\\Http\\JsonResponse',
        'updateStatus' => 'Illuminate\\Http\\JsonResponse',
        'updatePriority' => 'Illuminate\\Http\\JsonResponse',
        'bulkUpdateStatus' => 'Illuminate\\Http\\JsonResponse',
        'setDueDate' => 'Illuminate\\Http\\JsonResponse',
        'getAssignmentMessage' => 'string'
    ],
    'app/Http/Controllers/Admin/UserManagementController.php' => [
        'store' => 'Illuminate\\Http\\RedirectResponse',
        'show' => 'Illuminate\\Contracts\\View\\View',
        'edit' => 'Illuminate\\Contracts\\View\\View',
        'update' => 'Illuminate\\Http\\RedirectResponse',
        'destroy' => 'Illuminate\\Http\\RedirectResponse',
        'toggleStatus' => 'Illuminate\\Http\\JsonResponse',
        'resetPassword' => 'Illuminate\\Http\\JsonResponse',
        'bulkAction' => 'Illuminate\\Http\\JsonResponse',
        'impersonate' => 'Illuminate\\Http\\RedirectResponse',
        'sendVerification' => 'Illuminate\\Http\\JsonResponse',
        'inlineUpdate' => 'Illuminate\\Http\\JsonResponse',
        'updateRole' => 'Illuminate\\Http\\JsonResponse',
        'bulkRoleAssignment' => 'Illuminate\\Http\\JsonResponse',
        'bulkActivate' => 'Illuminate\\Http\\JsonResponse',
        'bulkDeactivate' => 'Illuminate\\Http\\JsonResponse',
        'bulkDelete' => 'Illuminate\\Http\\JsonResponse',
        'bulkAssignRole' => 'Illuminate\\Http\\JsonResponse',
        'bulkExport' => 'Illuminate\\Http\\Response'
    ],
    'app/Http/Controllers/AgentDashboardController.php' => [
        'getAgentMetrics' => 'Illuminate\\Http\\JsonResponse',
        'getTicketMonitoringData' => 'Illuminate\\Http\\JsonResponse',
        'getPurchaseQueueData' => 'Illuminate\\Http\\JsonResponse',
        'getAlertData' => 'Illuminate\\Http\\JsonResponse',
        'getRecentActivity' => 'array',
        'getPerformanceMetrics' => 'array',
        'getHighDemandEvents' => 'array'
    ]
];

// Fix parameter types for methods that also need parameter typing
$parameterFixes = [
    'app/Http/Controllers/Admin/ReportsController.php' => [
        'calculateMedian' => [
            'numbers' => 'array'
        ]
    ],
    'app/Http/Controllers/Admin/SystemController.php' => [
        'formatBytes' => 'string',
        'extractLogLevel' => 'string'
    ]
];

// Process all controller fixes
foreach ($controllerFixes as $file => $methods) {
    if (file_exists($file)) {
        echo "\nProcessing: $file\n";
        fixReturnTypes($file, $methods);
    } else {
        echo "\nFile not found: $file\n";
    }
}

echo "\n✅ Return type fixes completed!\n";
