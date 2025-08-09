<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PerformanceMetricsController extends Controller
{
    private $redis;
    
    // Metric thresholds for alerts
    const THRESHOLDS = [
        'lcp' => ['good' => 2500, 'poor' => 4000],        // Largest Contentful Paint
        'fid' => ['good' => 100, 'poor' => 300],          // First Input Delay
        'cls' => ['good' => 0.1, 'poor' => 0.25],         // Cumulative Layout Shift
        'fcp' => ['good' => 1800, 'poor' => 3000],        // First Contentful Paint
        'ttfb' => ['good' => 800, 'poor' => 1800],        // Time to First Byte
        'page_load' => ['good' => 3000, 'poor' => 6000],  // Page Load Time
    ];
    
    // Redis keys
    const METRICS_KEY = 'performance:metrics';
    const ALERTS_KEY = 'performance:alerts';
    const SUMMARY_KEY = 'performance:summary';
    const USER_SESSIONS_KEY = 'performance:sessions';
    
    public function __construct()
    {
        $this->redis = Redis::connection();
    }
    
    /**
     * Receive performance metrics from browser
     */
    public function receiveMetrics(Request $request)
    {
        try {
            $data = $request->validate([
                'metrics' => 'required|array',
                'page' => 'required|array',
                'session' => 'required|array',
                'device' => 'array',
            ]);
            
            $metrics = $data['metrics'];
            $page = $data['page'];
            $session = $data['session'];
            $device = $data['device'] ?? [];
            
            // Process each metric
            foreach ($metrics as $metric) {
                $this->processMetric($metric, $page, $session, $device);
            }
            
            // Update real-time summaries
            $this->updateRealTimeSummaries($metrics, $page);
            
            // Check for performance alerts
            $this->checkPerformanceAlerts($metrics, $page);
            
            // Store session information
            $this->updateSessionTracking($session, $page, $device);
            
            return response()->json([
                'success' => true,
                'processed' => count($metrics),
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to process performance metrics', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to process metrics'
            ], 500);
        }
    }
    
    /**
     * Process individual metric
     */
    private function processMetric(array $metric, array $page, array $session, array $device)
    {
        $metricData = [
            'type' => $metric['type'],
            'data' => $metric['data'],
            'timestamp' => $metric['timestamp'],
            'url' => $page['url'] ?? '',
            'title' => $page['title'] ?? '',
            'referrer' => $page['referrer'] ?? '',
            'user_agent' => $metric['userAgent'] ?? '',
            'session_id' => $session['id'] ?? uniqid(),
            'user_id' => auth()->id(),
            'device_info' => $device
        ];
        
        // Store in Redis with expiration
        $key = self::METRICS_KEY . ':' . date('Y-m-d-H');
        $this->redis->lpush($key, json_encode($metricData));
        $this->redis->expire($key, 86400 * 7); // Keep for 7 days
        
        // Process specific metric types
        switch ($metric['type']) {
            case 'web_vital':
                $this->processWebVital($metric['data'], $page, $session);
                break;
                
            case 'ajax_request':
                $this->processAjaxRequest($metric['data'], $page, $session);
                break;
                
            case 'long_task':
                $this->processLongTask($metric['data'], $page, $session);
                break;
                
            case 'custom_measurement':
                $this->processCustomMeasurement($metric['data'], $page, $session);
                break;
                
            case 'visibility_change':
                $this->processVisibilityChange($metric['data'], $page, $session);
                break;
        }
        
        // Update counters
        $this->updateMetricCounters($metric['type'], $page);
    }
    
    /**
     * Process Core Web Vital metrics
     */
    private function processWebVital(array $data, array $page, array $session)
    {
        $vitalName = strtolower($data['name']);
        $value = $data['value'];
        $rating = $data['rating'];
        
        // Store vital data
        $vitalKey = "performance:vitals:{$vitalName}:" . date('Y-m-d-H');
        $vitalData = [
            'value' => $value,
            'rating' => $rating,
            'url' => $page['url'],
            'timestamp' => now(),
            'user_id' => auth()->id()
        ];
        
        $this->redis->lpush($vitalKey, json_encode($vitalData));
        $this->redis->expire($vitalKey, 86400 * 30); // Keep for 30 days
        
        // Update running averages
        $this->updateVitalAverages($vitalName, $value, $page);
        
        // Check for poor performance
        if ($rating === 'poor') {
            $this->createPerformanceAlert('poor_web_vital', [
                'vital' => $vitalName,
                'value' => $value,
                'threshold' => self::THRESHOLDS[$vitalName]['poor'] ?? 0,
                'url' => $page['url'],
                'title' => $page['title'] ?? ''
            ]);
        }
    }
    
    /**
     * Process AJAX request metrics
     */
    private function processAjaxRequest(array $data, array $page, array $session)
    {
        $duration = $data['duration'];
        $url = $data['url'];
        $status = $data['status'];
        
        // Track API endpoint performance
        $endpointKey = "performance:api_endpoints:" . date('Y-m-d-H');
        $endpointData = [
            'url' => $url,
            'duration' => $duration,
            'status' => $status,
            'timestamp' => now(),
            'page' => $page['url']
        ];
        
        $this->redis->lpush($endpointKey, json_encode($endpointData));
        $this->redis->expire($endpointKey, 86400 * 7);
        
        // Alert on slow API requests
        if ($duration > 5000) { // > 5 seconds
            $this->createPerformanceAlert('slow_api_request', [
                'url' => $url,
                'duration' => $duration,
                'status' => $status,
                'page' => $page['url']
            ]);
        }
        
        // Track error rates
        if ($status >= 400) {
            $this->incrementErrorCounter($url, $status);
        }
    }
    
    /**
     * Process long task metrics (tasks > 50ms)
     */
    private function processLongTask(array $data, array $page, array $session)
    {
        $duration = $data['duration'];
        $attribution = $data['attribution'] ?? [];
        
        // Store long task data
        $taskKey = "performance:long_tasks:" . date('Y-m-d-H');
        $taskData = [
            'duration' => $duration,
            'startTime' => $data['startTime'],
            'attribution' => $attribution,
            'url' => $page['url'],
            'timestamp' => now()
        ];
        
        $this->redis->lpush($taskKey, json_encode($taskData));
        $this->redis->expire($taskKey, 86400 * 7);
        
        // Alert on very long tasks
        if ($duration > 500) { // > 500ms
            $this->createPerformanceAlert('very_long_task', [
                'duration' => $duration,
                'url' => $page['url'],
                'attribution' => $attribution
            ]);
        }
    }
    
    /**
     * Process custom performance measurements
     */
    private function processCustomMeasurement(array $data, array $page, array $session)
    {
        $name = $data['name'];
        $duration = $data['duration'];
        
        // Store custom measurement
        $measurementKey = "performance:custom:{$name}:" . date('Y-m-d-H');
        $measurementData = [
            'duration' => $duration,
            'startTime' => $data['startTime'],
            'url' => $page['url'],
            'timestamp' => now()
        ];
        
        $this->redis->lpush($measurementKey, json_encode($measurementData));
        $this->redis->expire($measurementKey, 86400 * 7);
        
        // Update measurement averages
        $this->updateMeasurementAverages($name, $duration, $page);
    }
    
    /**
     * Process visibility changes
     */
    private function processVisibilityChange(array $data, array $page, array $session)
    {
        $hidden = $data['hidden'];
        $visibilityState = $data['visibilityState'];
        
        // Track page visibility patterns
        $visibilityKey = "performance:visibility:" . date('Y-m-d');
        $visibilityData = [
            'hidden' => $hidden,
            'state' => $visibilityState,
            'url' => $page['url'],
            'timestamp' => now(),
            'user_id' => auth()->id()
        ];
        
        $this->redis->lpush($visibilityKey, json_encode($visibilityData));
        $this->redis->expire($visibilityKey, 86400 * 30);
    }
    
    /**
     * Update real-time performance summaries
     */
    private function updateRealTimeSummaries(array $metrics, array $page)
    {
        $summaryKey = self::SUMMARY_KEY . ':' . date('Y-m-d-H');
        
        // Get existing summary or create new
        $summary = $this->redis->get($summaryKey);
        if ($summary) {
            $summary = json_decode($summary, true);
        } else {
            $summary = [
                'total_metrics' => 0,
                'unique_pages' => [],
                'metric_types' => [],
                'web_vitals' => [],
                'errors' => 0,
                'last_updated' => now()
            ];
        }
        
        // Update summary
        $summary['total_metrics'] += count($metrics);
        $summary['unique_pages'][$page['url']] = true;
        $summary['last_updated'] = now();
        
        foreach ($metrics as $metric) {
            $type = $metric['type'];
            $summary['metric_types'][$type] = ($summary['metric_types'][$type] ?? 0) + 1;
            
            if ($type === 'web_vital') {
                $vitalName = $metric['data']['name'];
                $summary['web_vitals'][$vitalName] = ($summary['web_vitals'][$vitalName] ?? 0) + 1;
            }
        }
        
        // Store updated summary
        $this->redis->setex($summaryKey, 86400, json_encode($summary));
    }
    
    /**
     * Check for performance alerts
     */
    private function checkPerformanceAlerts(array $metrics, array $page)
    {
        $webVitals = collect($metrics)->where('type', 'web_vital');
        
        $poorVitals = $webVitals->filter(function ($metric) {
            return $metric['data']['rating'] === 'poor';
        });
        
        if ($poorVitals->count() >= 2) {
            $this->createPerformanceAlert('multiple_poor_vitals', [
                'vitals' => $poorVitals->pluck('data.name')->toArray(),
                'url' => $page['url'],
                'count' => $poorVitals->count()
            ]);
        }
    }
    
    /**
     * Create performance alert
     */
    private function createPerformanceAlert(string $type, array $data)
    {
        $alert = [
            'type' => $type,
            'data' => $data,
            'timestamp' => now(),
            'severity' => $this->getAlertSeverity($type),
            'user_id' => auth()->id(),
            'resolved' => false
        ];
        
        // Store alert
        $alertKey = self::ALERTS_KEY . ':' . date('Y-m-d');
        $this->redis->lpush($alertKey, json_encode($alert));
        $this->redis->expire($alertKey, 86400 * 30);
        
        // Log critical alerts
        if ($alert['severity'] === 'critical') {
            Log::warning('Critical performance alert', $alert);
        }
    }
    
    /**
     * Get alert severity based on type
     */
    private function getAlertSeverity(string $type): string
    {
        $severityMap = [
            'poor_web_vital' => 'warning',
            'slow_api_request' => 'warning',
            'very_long_task' => 'error',
            'multiple_poor_vitals' => 'critical',
            'high_error_rate' => 'critical'
        ];
        
        return $severityMap[$type] ?? 'info';
    }
    
    /**
     * Update session tracking
     */
    private function updateSessionTracking(array $session, array $page, array $device)
    {
        $sessionId = $session['id'] ?? uniqid();
        $sessionKey = self::USER_SESSIONS_KEY . ":{$sessionId}";
        
        $sessionData = [
            'id' => $sessionId,
            'user_id' => auth()->id(),
            'start_time' => $session['timestamp'],
            'last_activity' => now(),
            'pages_visited' => [$page['url']],
            'device_info' => $device,
            'total_time' => $session['timeOnPage'] ?? 0
        ];
        
        // Update or create session
        $existingSession = $this->redis->get($sessionKey);
        if ($existingSession) {
            $existingSession = json_decode($existingSession, true);
            $sessionData['pages_visited'] = array_unique(
                array_merge($existingSession['pages_visited'], [$page['url']])
            );
            $sessionData['start_time'] = $existingSession['start_time'];
        }
        
        $this->redis->setex($sessionKey, 86400, json_encode($sessionData));
    }
    
    /**
     * Get performance dashboard data
     */
    public function getDashboardData(Request $request)
    {
        $period = $request->get('period', '24h');
        
        try {
            $data = [
                'overview' => $this->getPerformanceOverview($period),
                'web_vitals' => $this->getWebVitalsData($period),
                'page_performance' => $this->getPagePerformanceData($period),
                'api_performance' => $this->getApiPerformanceData($period),
                'user_sessions' => $this->getUserSessionsData($period),
                'alerts' => $this->getRecentAlerts($period),
                'trends' => $this->getPerformanceTrends($period),
            ];
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            Log::error('Failed to get performance dashboard data', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to load dashboard data'
            ], 500);
        }
    }
    
    /**
     * Get performance overview
     */
    private function getPerformanceOverview(string $period): array
    {
        $hours = $this->getPeriodHours($period);
        $keys = [];
        
        // Generate keys for the period
        for ($i = 0; $i < $hours; $i++) {
            $time = Carbon::now()->subHours($i);
            $keys[] = self::SUMMARY_KEY . ':' . $time->format('Y-m-d-H');
        }
        
        $totalMetrics = 0;
        $uniquePages = [];
        $metricTypes = [];
        
        foreach ($keys as $key) {
            $summary = $this->redis->get($key);
            if ($summary) {
                $summary = json_decode($summary, true);
                $totalMetrics += $summary['total_metrics'] ?? 0;
                $uniquePages = array_merge($uniquePages, array_keys($summary['unique_pages'] ?? []));
                
                foreach ($summary['metric_types'] ?? [] as $type => $count) {
                    $metricTypes[$type] = ($metricTypes[$type] ?? 0) + $count;
                }
            }
        }
        
        return [
            'total_metrics' => $totalMetrics,
            'unique_pages' => count(array_unique($uniquePages)),
            'metric_types' => $metricTypes,
            'period' => $period
        ];
    }
    
    /**
     * Get Web Vitals data
     */
    private function getWebVitalsData(string $period): array
    {
        $vitals = ['lcp', 'fid', 'cls', 'fcp'];
        $hours = $this->getPeriodHours($period);
        $data = [];
        
        foreach ($vitals as $vital) {
            $values = [];
            $ratings = ['good' => 0, 'needs-improvement' => 0, 'poor' => 0];
            
            for ($i = 0; $i < $hours; $i++) {
                $time = Carbon::now()->subHours($i);
                $key = "performance:vitals:{$vital}:" . $time->format('Y-m-d-H');
                
                $vitalData = $this->redis->lrange($key, 0, -1);
                foreach ($vitalData as $item) {
                    $item = json_decode($item, true);
                    $values[] = $item['value'];
                    $ratings[$item['rating']]++;
                }
            }
            
            $data[$vital] = [
                'average' => !empty($values) ? array_sum($values) / count($values) : 0,
                'count' => count($values),
                'ratings' => $ratings,
                'threshold' => self::THRESHOLDS[$vital] ?? null
            ];
        }
        
        return $data;
    }
    
    /**
     * Get page performance data
     */
    private function getPagePerformanceData(string $period): array
    {
        // This would require aggregating metrics by page URL
        // For now, return sample data structure
        return [
            'top_pages' => [],
            'slowest_pages' => [],
            'most_visited' => []
        ];
    }
    
    /**
     * Get API performance data
     */
    private function getApiPerformanceData(string $period): array
    {
        $hours = $this->getPeriodHours($period);
        $endpoints = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $time = Carbon::now()->subHours($i);
            $key = "performance:api_endpoints:" . $time->format('Y-m-d-H');
            
            $apiData = $this->redis->lrange($key, 0, -1);
            foreach ($apiData as $item) {
                $item = json_decode($item, true);
                $url = $item['url'];
                
                if (!isset($endpoints[$url])) {
                    $endpoints[$url] = [
                        'requests' => 0,
                        'total_duration' => 0,
                        'errors' => 0
                    ];
                }
                
                $endpoints[$url]['requests']++;
                $endpoints[$url]['total_duration'] += $item['duration'];
                
                if ($item['status'] >= 400) {
                    $endpoints[$url]['errors']++;
                }
            }
        }
        
        // Calculate averages and sort
        foreach ($endpoints as $url => &$data) {
            $data['average_duration'] = $data['total_duration'] / $data['requests'];
            $data['error_rate'] = ($data['errors'] / $data['requests']) * 100;
        }
        
        return $endpoints;
    }
    
    /**
     * Get user sessions data
     */
    private function getUserSessionsData(string $period): array
    {
        // This would require aggregating session data
        // For now, return sample structure
        return [
            'total_sessions' => 0,
            'average_session_time' => 0,
            'bounce_rate' => 0
        ];
    }
    
    /**
     * Get recent alerts
     */
    private function getRecentAlerts(string $period): array
    {
        $days = ceil($this->getPeriodHours($period) / 24);
        $alerts = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($i);
            $key = self::ALERTS_KEY . ':' . $date->format('Y-m-d');
            
            $dayAlerts = $this->redis->lrange($key, 0, -1);
            foreach ($dayAlerts as $alert) {
                $alerts[] = json_decode($alert, true);
            }
        }
        
        // Sort by timestamp (newest first)
        usort($alerts, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($alerts, 0, 50); // Return latest 50 alerts
    }
    
    /**
     * Get performance trends
     */
    private function getPerformanceTrends(string $period): array
    {
        // This would calculate trends over time
        // For now, return sample structure
        return [
            'web_vitals_trend' => [],
            'page_load_trend' => [],
            'error_rate_trend' => []
        ];
    }
    
    // Helper methods
    
    private function getPeriodHours(string $period): int
    {
        switch ($period) {
            case '1h': return 1;
            case '6h': return 6;
            case '24h': return 24;
            case '7d': return 168;
            case '30d': return 720;
            default: return 24;
        }
    }
    
    private function updateVitalAverages(string $vital, float $value, array $page)
    {
        $key = "performance:vitals:{$vital}:average:" . date('Y-m-d');
        
        $avg = $this->redis->hget($key, 'average') ?: 0;
        $count = $this->redis->hget($key, 'count') ?: 0;
        
        $newCount = $count + 1;
        $newAverage = (($avg * $count) + $value) / $newCount;
        
        $this->redis->hmset($key, [
            'average' => $newAverage,
            'count' => $newCount,
            'last_updated' => now()
        ]);
        
        $this->redis->expire($key, 86400 * 30);
    }
    
    private function updateMeasurementAverages(string $name, float $duration, array $page)
    {
        $key = "performance:custom:{$name}:average:" . date('Y-m-d');
        
        $avg = $this->redis->hget($key, 'average') ?: 0;
        $count = $this->redis->hget($key, 'count') ?: 0;
        
        $newCount = $count + 1;
        $newAverage = (($avg * $count) + $duration) / $newCount;
        
        $this->redis->hmset($key, [
            'average' => $newAverage,
            'count' => $newCount,
            'last_updated' => now()
        ]);
        
        $this->redis->expire($key, 86400 * 30);
    }
    
    private function updateMetricCounters(string $type, array $page)
    {
        $counterKey = "performance:counters:" . date('Y-m-d');
        $this->redis->hincrby($counterKey, $type, 1);
        $this->redis->expire($counterKey, 86400 * 30);
    }
    
    private function incrementErrorCounter(string $url, int $status)
    {
        $errorKey = "performance:errors:" . date('Y-m-d-H');
        $errorData = [
            'url' => $url,
            'status' => $status,
            'timestamp' => now()
        ];
        
        $this->redis->lpush($errorKey, json_encode($errorData));
        $this->redis->expire($errorKey, 86400 * 7);
    }
}
