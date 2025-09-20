<?php declare(strict_types=1);

$files = [
    'app/Http/Controllers/AgentDashboardController.php' => [
        'public function index()'                   => 'public function index(): \\Illuminate\\Contracts\\View\\View',
        'public function getAgentMetrics()'         => 'public function getAgentMetrics(): \\Illuminate\\Http\\JsonResponse',
        'public function getTicketMonitoringData()' => 'public function getTicketMonitoringData(): \\Illuminate\\Http\\JsonResponse',
        'public function getPurchaseQueueData()'    => 'public function getPurchaseQueueData(): \\Illuminate\\Http\\JsonResponse',
        'public function getAlertData()'            => 'public function getAlertData(): \\Illuminate\\Http\\JsonResponse',
        'public function getRecentActivity()'       => 'public function getRecentActivity(): \\Illuminate\\Http\\JsonResponse',
        'public function getPerformanceMetrics()'   => 'public function getPerformanceMetrics(): \\Illuminate\\Http\\JsonResponse',
    ],
    'app/Http/Controllers/Ajax/DashboardController.php' => [
        'public function liveTickets()'            => 'public function liveTickets(): \\Illuminate\\Http\\JsonResponse',
        'public function userRecommendations()'    => 'public function userRecommendations(): \\Illuminate\\Http\\JsonResponse',
        'public function platformHealth()'         => 'public function platformHealth(): \\Illuminate\\Http\\JsonResponse',
        'public function priceAlerts()'            => 'public function priceAlerts(): \\Illuminate\\Http\\JsonResponse',
        'public function getUserRecommendations()' => 'public function getUserRecommendations(): array',
        'public function detectPriceDrop()'        => 'public function detectPriceDrop(): bool',
    ],
    'app/Http/Controllers/Api/AdvancedReportingController.php' => [
        'protected $reportingService;'     => 'protected \\App\\Services\\AdvancedReportingService $reportingService;',
        'public function downloadReport()' => 'public function downloadReport(): \\Symfony\\Component\\HttpFoundation\\StreamedResponse',
    ],
    'app/Http/Controllers/Api/AlertController.php' => [
        'protected $alertSystem;' => 'protected \\App\\Services\\AdvancedAlertSystem $alertSystem;',
    ],
    'app/Http/Controllers/Api/AnalyticsController.php' => [
        'protected $platformMonitoringService;' => 'protected \\App\\Services\\PlatformMonitoringService $platformMonitoringService;',
    ],
    'app/Http/Controllers/Api/AdvancedAnalyticsController.php' => [
        'protected $analyticsDashboard;'              => 'protected \\App\\Services\\AdvancedAnalyticsDashboard $analyticsDashboard;',
        'public function buildFilters()'              => 'public function buildFilters(): array',
        'public function buildFiltersFromTimeRange()' => 'public function buildFiltersFromTimeRange(): array',
    ],
];

foreach ($files as $file => $replacements) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        file_put_contents($file, $content);
        echo "Updated {$file}\n";
    }
}
