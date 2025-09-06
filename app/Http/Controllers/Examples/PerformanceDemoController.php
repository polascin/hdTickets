<?php

namespace App\Http\Controllers\Examples;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PerformanceDemoController extends Controller
{
    /**
     * Show the performance optimization demo page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('examples.performance-demo');
    }
    
    /**
     * Generate sample content for lazy loading demo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sampleContent(Request $request)
    {
        // Simulate a slight delay
        usleep(300000); // 300ms delay
        
        return response()->json([
            'html' => '<div class="p-lg text-center">
                <img src="https://via.placeholder.com/150/4F46E5/FFFFFF?text=Dynamic" class="mx-auto mb-sm" alt="Dynamic content" />
                <h4 class="font-semibold">Dynamically Loaded Content</h4>
                <p class="text-sm text-gray-600">This content was loaded via AJAX when scrolled into view.</p>
            </div>'
        ]);
    }
    
    /**
     * Search API endpoint for the debounce demo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');
        
        // Validate query
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }
        
        // Check cache first
        $cacheKey = 'search_' . md5($query);
        if (Cache::has($cacheKey)) {
            return response()->json([
                'results' => Cache::get($cacheKey),
                'from_cache' => true
            ]);
        }
        
        // Simulate processing time
        usleep(rand(200000, 500000)); // 200-500ms delay
        
        // Generate mock results
        $events = [
            'NBA Lakers vs Warriors',
            'NFL Super Bowl',
            'Premier League - Manchester United',
            'UEFA Champions League Final',
            'MLB World Series',
            'Wimbledon Tennis Championship',
            'Formula 1 Grand Prix',
            'NHL Stanley Cup',
            'Olympic Games Opening Ceremony',
            'FIFA World Cup Final'
        ];
        
        // Filter results based on query
        $results = array_filter($events, function($event) use ($query) {
            return stripos($event, $query) !== false;
        });
        
        // Format results
        $formattedResults = [];
        foreach ($results as $index => $event) {
            $formattedResults[] = [
                'id' => $index + 1,
                'title' => $event,
                'price' => '$' . (rand(50, 500)),
                'availability' => rand(1, 10) > 3 ? 'Available' : 'Limited'
            ];
        }
        
        // Add some fallback results if no matches
        if (empty($formattedResults)) {
            $formattedResults = [
                [
                    'id' => 1,
                    'title' => "Best tickets for {$query}",
                    'price' => '$' . (rand(50, 500)),
                    'availability' => 'Available'
                ],
                [
                    'id' => 2,
                    'title' => "{$query} Championship Tickets",
                    'price' => '$' . (rand(50, 500)),
                    'availability' => 'Limited'
                ]
            ];
        }
        
        // Cache results
        Cache::put($cacheKey, $formattedResults, now()->addMinutes(30));
        
        return response()->json([
            'results' => $formattedResults,
            'from_cache' => false
        ]);
    }
    
    /**
     * API endpoint that provides performance metrics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function metrics()
    {
        // Get some real server metrics
        $metrics = [
            'memory_usage' => memory_get_usage(true) / 1024 / 1024, // MB
            'peak_memory' => memory_get_peak_usage(true) / 1024 / 1024, // MB
            'server_time' => microtime(true),
            'cpu_load' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            'php_version' => PHP_VERSION
        ];
        
        return response()->json($metrics);
    }
    
    /**
     * Clear search cache
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearSearchCache()
    {
        // Get all cache keys starting with 'search_'
        $keys = Cache::getRedis()->keys('laravel_cache:search_*');
        
        // Format them to remove the prefix
        $keys = array_map(function($key) {
            return str_replace('laravel_cache:', '', $key);
        }, $keys);
        
        // Clear these keys
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Search cache cleared',
            'keys_cleared' => count($keys)
        ]);
    }
}
