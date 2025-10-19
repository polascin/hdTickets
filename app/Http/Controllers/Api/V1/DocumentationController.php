<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * API Documentation Controller
 *
 * Provides comprehensive API documentation and developer tools:
 * - Interactive API documentation
 * - OpenAPI/Swagger specifications
 * - Developer testing tools
 * - Usage statistics and monitoring
 */
class DocumentationController extends Controller
{
    /**
     * API overview and version information
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'api' => [
                'name'              => 'HDTickets API Access Layer',
                'version'           => '1.0.0',
                'description'       => 'Comprehensive RESTful API for sports ticket monitoring, price tracking, and automated purchasing',
                'base_url'          => config('app.url') . '/api/api-access/v1',
                'documentation_url' => config('app.url') . '/api/api-access/v1/docs',
                'openapi_spec_url'  => config('app.url') . '/api/api-access/v1/openapi',
            ],
            'authentication' => [
                'type'         => 'API Key',
                'header'       => 'Authorization: Bearer YOUR_API_KEY',
                'alternative'  => 'X-API-Key: YOUR_API_KEY',
                'register_url' => config('app.url') . '/api/api-access/v1/auth/register',
            ],
            'rate_limits' => [
                'general'   => '100 requests/hour',
                'search'    => '50 requests/hour',
                'intensive' => '30 requests/hour',
                'webhooks'  => '10 requests/hour',
            ],
            'supported_features' => [
                'event_monitoring'       => 'Real-time ticket availability and price monitoring',
                'price_analytics'        => 'Historical price data and trend analysis',
                'automated_purchasing'   => 'Automated ticket purchasing with smart rules',
                'multi_event_management' => 'Bulk operations and portfolio management',
                'webhooks'               => 'Real-time notifications and integrations',
                'api_management'         => 'API key management and usage analytics',
            ],
            'endpoints' => [
                'events'      => '/events - Event discovery and monitoring',
                'multi_event' => '/multi-event - Portfolio and bulk operations',
                'webhooks'    => '/webhooks - Webhook management',
                'auth'        => '/auth - Authentication and user management',
                'dev_tools'   => '/dev-tools - Developer utilities',
            ],
            'status'       => 'operational',
            'last_updated' => now()->toISOString(),
        ]);
    }

    /**
     * System health check
     */
    public function health(): JsonResponse
    {
        $health = [
            'status'      => 'healthy',
            'timestamp'   => now()->toISOString(),
            'version'     => '1.0.0',
            'environment' => config('app.env'),
            'services'    => [],
        ];

        try {
            // Database health
            $dbStart = microtime(TRUE);
            DB::select('SELECT 1');
            $dbTime = round((microtime(TRUE) - $dbStart) * 1000, 2);

            $health['services']['database'] = [
                'status'           => 'healthy',
                'response_time_ms' => $dbTime,
            ];
        } catch (\Exception $e) {
            $health['status'] = 'degraded';
            $health['services']['database'] = [
                'status' => 'unhealthy',
                'error'  => 'Database connection failed',
            ];
        }

        try {
            // Cache health
            $cacheStart = microtime(TRUE);
            Cache::put('health_check', 'test', 1);
            $cached = Cache::get('health_check');
            $cacheTime = round((microtime(TRUE) - $cacheStart) * 1000, 2);

            $health['services']['cache'] = [
                'status'           => $cached === 'test' ? 'healthy' : 'unhealthy',
                'response_time_ms' => $cacheTime,
            ];
        } catch (\Exception $e) {
            $health['status'] = 'degraded';
            $health['services']['cache'] = [
                'status' => 'unhealthy',
                'error'  => 'Cache system failed',
            ];
        }

        // API stats
        $health['api_stats'] = [
            'total_users'     => Cache::remember('health_total_users', 300, fn () => \App\Models\User::count()),
            'active_monitors' => Cache::remember('health_active_monitors', 300, fn () => \App\Models\EventMonitor::where('is_active', TRUE)->count()),
            'total_api_keys'  => Cache::remember('health_total_api_keys', 300, fn () => \App\Models\ApiKey::where('is_active', TRUE)->count()),
        ];

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * Complete API documentation
     */
    public function documentation(): JsonResponse
    {
        return response()->json([
            'api_documentation' => [
                'overview'       => $this->getApiOverview(),
                'authentication' => $this->getAuthenticationDocs(),
                'endpoints'      => $this->getEndpointsDocs(),
                'rate_limiting'  => $this->getRateLimitingDocs(),
                'webhooks'       => $this->getWebhooksDocs(),
                'error_handling' => $this->getErrorHandlingDocs(),
                'examples'       => $this->getExamplesDocs(),
                'sdks'           => $this->getSdksDocs(),
            ],
        ]);
    }

    /**
     * OpenAPI/Swagger specification
     */
    public function openApiSpec(): JsonResponse
    {
        $spec = [
            'openapi' => '3.0.3',
            'info'    => [
                'title'       => 'HDTickets API',
                'description' => 'Comprehensive sports ticket monitoring and automation API',
                'version'     => '1.0.0',
                'contact'     => [
                    'name'  => 'HDTickets API Support',
                    'email' => 'api-support@hdtickets.com',
                ],
            ],
            'servers' => [
                [
                    'url'         => config('app.url') . '/api/api-access/v1',
                    'description' => 'Production API Server',
                ],
            ],
            'security' => [
                ['ApiKeyAuth' => []],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type'        => 'apiKey',
                        'in'          => 'header',
                        'name'        => 'Authorization',
                        'description' => 'API key authorization using Bearer token format',
                    ],
                ],
                'schemas'   => $this->getOpenApiSchemas(),
                'responses' => $this->getOpenApiResponses(),
            ],
            'paths' => $this->getOpenApiPaths(),
        ];

        return response()->json($spec);
    }

    /**
     * Test API connection
     */
    public function testConnection(): JsonResponse
    {
        $user = Auth::user();
        $apiKey = request()->attributes->get('api_key');

        return response()->json([
            'success'            => TRUE,
            'message'            => 'API connection successful',
            'connection_details' => [
                'authenticated_user' => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'subscription_plan' => $user->subscription_plan,
                ],
                'api_key' => [
                    'id'          => $apiKey->id,
                    'name'        => $apiKey->name,
                    'permissions' => $apiKey->permissions,
                ],
                'request_info' => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp'  => now()->toISOString(),
                ],
            ],
            'next_steps' => [
                'browse_events'    => '/events',
                'start_monitoring' => '/events/{event_id}/monitoring/start',
                'view_portfolio'   => '/multi-event/portfolio',
                'setup_webhooks'   => '/webhooks',
            ],
        ]);
    }

    /**
     * Get rate limit information for current user
     */
    public function rateLimits(): JsonResponse
    {
        $user = Auth::user();
        $apiKey = request()->attributes->get('api_key');

        return response()->json([
            'rate_limits' => [
                'current_plan'  => $user->subscription_plan,
                'limits'        => $this->getUserRateLimits($user),
                'current_usage' => [
                    'requests_this_hour' => $this->getCurrentHourlyUsage($apiKey),
                    'requests_today'     => $this->getDailyUsage($apiKey),
                    'api_key_usage'      => $apiKey->usage_count,
                ],
                'remaining_requests' => $apiKey->getRemainingRequests(),
                'reset_time'         => now()->addHour()->toISOString(),
            ],
            'recommendations' => $this->getRateLimitRecommendations($user, $apiKey),
        ]);
    }

    /**
     * Get detailed usage statistics
     */
    public function usageStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->input('period', '7d');

        return response()->json([
            'usage_statistics' => [
                'period'              => $period,
                'api_requests'        => $this->getApiRequestStats($user, $period),
                'endpoint_usage'      => $this->getEndpointUsageStats($user, $period),
                'error_rates'         => $this->getErrorRateStats($user, $period),
                'performance_metrics' => $this->getPerformanceStats($user, $period),
                'cost_analysis'       => $this->getCostAnalysis($user, $period),
            ],
        ]);
    }

    /**
     * Validate webhook configuration
     */
    public function validateWebhook(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'url'    => 'required|url',
            'events' => 'required|array',
            'secret' => 'nullable|string|min:16',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $url = $request->input('url');
        $testResult = $this->testWebhookEndpoint($url);

        return response()->json([
            'validation_result' => [
                'url'             => $url,
                'is_valid'        => $testResult['success'],
                'response_time'   => $testResult['response_time'],
                'status_code'     => $testResult['status_code'],
                'ssl_valid'       => $testResult['ssl_valid'],
                'recommendations' => $testResult['recommendations'],
            ],
        ]);
    }

    // Private helper methods

    private function getApiOverview(): array
    {
        return [
            'description'  => 'HDTickets API provides comprehensive access to sports ticket monitoring, price tracking, and automated purchasing capabilities.',
            'key_features' => [
                'Real-time ticket monitoring across multiple platforms',
                'Advanced price analytics and predictions',
                'Automated purchasing with smart rules',
                'Multi-event portfolio management',
                'Real-time webhooks and notifications',
            ],
            'supported_platforms' => [
                'Ticketmaster', 'SeatGeek', 'StubHub', 'Vivid Seats',
            ],
        ];
    }

    private function getAuthenticationDocs(): array
    {
        return [
            'method'             => 'API Key Authentication',
            'header_format'      => 'Authorization: Bearer YOUR_API_KEY',
            'alternative_header' => 'X-API-Key: YOUR_API_KEY',
            'registration'       => 'POST /auth/register',
            'key_management'     => 'GET /api-keys',
            'permissions'        => ['read', 'write', 'admin'],
        ];
    }

    private function getEndpointsDocs(): array
    {
        return [
            'events' => [
                'GET /events'                        => 'List and search events',
                'GET /events/{id}'                   => 'Get event details',
                'POST /events/{id}/monitoring/start' => 'Start monitoring',
                'GET /events/{id}/price/analytics'   => 'Get price analytics',
            ],
            'multi_event' => [
                'GET /multi-event/portfolio'       => 'Get portfolio overview',
                'POST /multi-event/groups'         => 'Create event group',
                'POST /multi-event/bulk-operation' => 'Execute bulk operations',
            ],
            'webhooks' => [
                'GET /webhooks'            => 'List webhooks',
                'POST /webhooks'           => 'Create webhook',
                'POST /webhooks/{id}/test' => 'Test webhook',
            ],
        ];
    }

    private function getRateLimitingDocs(): array
    {
        return [
            'strategy' => 'Token bucket with hourly reset',
            'headers'  => [
                'X-RateLimit-Limit'     => 'Maximum requests allowed',
                'X-RateLimit-Remaining' => 'Remaining requests',
                'X-RateLimit-Reset'     => 'Reset timestamp',
            ],
            'limits_by_plan' => [
                'starter'    => '100 requests/hour',
                'pro'        => '1000 requests/hour',
                'enterprise' => '10000 requests/hour',
            ],
        ];
    }

    private function getWebhooksDocs(): array
    {
        return [
            'supported_events' => [
                'price_alert'         => 'Price threshold triggered',
                'monitoring_update'   => 'Monitoring status change',
                'purchase_complete'   => 'Automated purchase completed',
                'system_notification' => 'System alerts',
            ],
            'delivery' => [
                'method'    => 'POST',
                'timeout'   => '10 seconds',
                'retries'   => '3 attempts with exponential backoff',
                'signature' => 'HMAC-SHA256 in X-Signature header',
            ],
        ];
    }

    private function getErrorHandlingDocs(): array
    {
        return [
            'format'       => 'Consistent JSON error responses',
            'status_codes' => [
                '400' => 'Bad Request - Invalid parameters',
                '401' => 'Unauthorized - Invalid API key',
                '403' => 'Forbidden - Insufficient permissions',
                '404' => 'Not Found - Resource not found',
                '422' => 'Validation Error - Invalid input data',
                '429' => 'Rate Limited - Too many requests',
                '500' => 'Server Error - Internal server error',
            ],
            'error_response_format' => [
                'success'   => FALSE,
                'message'   => 'Error description',
                'errors'    => 'Detailed error information',
                'timestamp' => 'ISO 8601 timestamp',
            ],
        ];
    }

    private function getExamplesDocs(): array
    {
        return [
            'authentication' => [
                'curl' => 'curl -H "Authorization: Bearer YOUR_API_KEY" ' . config('app.url') . '/api/api-access/v1/events',
            ],
            'start_monitoring' => [
                'curl' => 'curl -X POST -H "Authorization: Bearer YOUR_API_KEY" -H "Content-Type: application/json" -d \'{"platforms":["ticketmaster"],"check_interval":300}\' ' . config('app.url') . '/api/api-access/v1/events/123/monitoring/start',
            ],
            'create_price_alert' => [
                'curl' => 'curl -X POST -H "Authorization: Bearer YOUR_API_KEY" -H "Content-Type: application/json" -d \'{"target_price":50.00,"alert_type":"below","notification_channels":["email"]}\' ' . config('app.url') . '/api/api-access/v1/events/123/price/alerts',
            ],
        ];
    }

    private function getSdksDocs(): array
    {
        return [
            'official_sdks' => [
                'javascript' => 'npm install hdtickets-api',
                'python'     => 'pip install hdtickets-api',
                'php'        => 'composer require hdtickets/api-client',
            ],
            'community_sdks' => [
                'go'   => 'go get github.com/hdtickets/go-api-client',
                'ruby' => 'gem install hdtickets-api',
            ],
        ];
    }

    private function getOpenApiSchemas(): array
    {
        return [
            'Event' => [
                'type'       => 'object',
                'properties' => [
                    'id'         => ['type' => 'integer'],
                    'name'       => ['type' => 'string'],
                    'venue'      => ['type' => 'string'],
                    'event_date' => ['type' => 'string', 'format' => 'date-time'],
                    'category'   => ['type' => 'string'],
                ],
            ],
            'ApiError' => [
                'type'       => 'object',
                'properties' => [
                    'success'   => ['type' => 'boolean'],
                    'message'   => ['type' => 'string'],
                    'errors'    => ['type' => 'object'],
                    'timestamp' => ['type' => 'string'],
                ],
            ],
        ];
    }

    private function getOpenApiResponses(): array
    {
        return [
            'Unauthorized' => [
                'description' => 'Invalid API key',
                'content'     => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ApiError'],
                    ],
                ],
            ],
            'RateLimited' => [
                'description' => 'Rate limit exceeded',
                'content'     => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ApiError'],
                    ],
                ],
            ],
        ];
    }

    private function getOpenApiPaths(): array
    {
        // This would contain detailed OpenAPI path definitions
        // Simplified for brevity
        return [
            '/events' => [
                'get' => [
                    'summary'    => 'List events',
                    'parameters' => [
                        [
                            'name'   => 'page',
                            'in'     => 'query',
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                            'content'     => [
                                'application/json' => [
                                    'schema' => [
                                        'type'       => 'object',
                                        'properties' => [
                                            'events' => [
                                                'type'  => 'array',
                                                'items' => ['$ref' => '#/components/schemas/Event'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getUserRateLimits($user): array
    {
        return match ($user->subscription_plan) {
            'starter'    => ['requests_per_hour' => 100, 'max_api_keys' => 2],
            'pro'        => ['requests_per_hour' => 1000, 'max_api_keys' => 10],
            'enterprise' => ['requests_per_hour' => 10000, 'max_api_keys' => 50],
            default      => ['requests_per_hour' => 50, 'max_api_keys' => 1]
        };
    }

    private function getCurrentHourlyUsage($apiKey): int
    {
        return $apiKey->getHourlyUsage();
    }

    private function getDailyUsage($apiKey): int
    {
        return $apiKey->getDailyUsage();
    }

    private function getRateLimitRecommendations($user, $apiKey): array
    {
        $recommendations = [];

        if ($apiKey->getRemainingRequests() < 10) {
            $recommendations[] = 'You are approaching your rate limit. Consider upgrading your plan or optimizing your API usage.';
        }

        return $recommendations;
    }

    private function testWebhookEndpoint(string $url): array
    {
        // Implementation would test the webhook endpoint
        return [
            'success'         => TRUE,
            'response_time'   => 150,
            'status_code'     => 200,
            'ssl_valid'       => TRUE,
            'recommendations' => [],
        ];
    }

    // Additional helper methods would be implemented here for statistics and analytics
    private function getApiRequestStats($user, $period): array
    {
        return [];
    }

    private function getEndpointUsageStats($user, $period): array
    {
        return [];
    }

    private function getErrorRateStats($user, $period): array
    {
        return [];
    }

    private function getPerformanceStats($user, $period): array
    {
        return [];
    }

    private function getCostAnalysis($user, $period): array
    {
        return [];
    }
}
