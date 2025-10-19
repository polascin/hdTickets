<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Webhooks API Controller
 *
 * Manages webhook endpoints for real-time notifications:
 * - Webhook CRUD operations
 * - Event subscription management
 * - Delivery tracking and retry logic
 * - Webhook testing and validation
 */
class WebhooksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List user's webhooks
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $webhooks = Webhook::where('user_id', $user->id)
                ->when($request->filled('is_active'), function ($query) use ($request) {
                    $query->where('is_active', $request->boolean('is_active'));
                })
                ->when($request->filled('event_type'), function ($query) use ($request) {
                    $query->whereJsonContains('events', $request->input('event_type'));
                })
                ->with(['logs' => function ($query) {
                    $query->latest()->limit(5);
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse([
                'webhooks' => $webhooks->map(function ($webhook) {
                    return [
                        'id'                => $webhook->id,
                        'name'              => $webhook->name,
                        'url'               => $webhook->url,
                        'events'            => $webhook->events,
                        'is_active'         => $webhook->is_active,
                        'last_delivery'     => $webhook->last_delivery_at,
                        'success_rate'      => $webhook->getSuccessRate(),
                        'total_deliveries'  => $webhook->total_deliveries,
                        'failed_deliveries' => $webhook->failed_deliveries,
                        'created_at'        => $webhook->created_at,
                        'status'            => $webhook->getStatus(),
                    ];
                }),
                'summary' => [
                    'total_webhooks'   => $webhooks->count(),
                    'active_webhooks'  => $webhooks->where('is_active', TRUE)->count(),
                    'healthy_webhooks' => $webhooks->filter(fn ($w) => $w->getSuccessRate() > 95)->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve webhooks', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create new webhook
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255',
            'url'                           => 'required|url|max:255',
            'events'                        => 'required|array|min:1',
            'events.*'                      => 'string|in:price_alert,monitoring_update,purchase_complete,system_notification,ticket_available,price_drop',
            'secret'                        => 'nullable|string|min:16|max:255',
            'is_active'                     => 'boolean',
            'retry_policy'                  => 'array',
            'retry_policy.max_attempts'     => 'integer|min:1|max:10',
            'retry_policy.backoff_strategy' => 'string|in:linear,exponential',
            'headers'                       => 'array',
            'timeout'                       => 'integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();

            // Check webhook limit based on subscription
            $maxWebhooks = $this->getMaxWebhooks($user);
            if ($user->webhooks()->count() >= $maxWebhooks) {
                return $this->errorResponse('Webhook limit reached', [
                    'max_webhooks' => $maxWebhooks,
                    'current_plan' => $user->subscription_plan,
                ], 403);
            }

            // Generate webhook secret if not provided
            $secret = $request->input('secret') ?: Str::random(32);

            $webhook = Webhook::create([
                'user_id'      => $user->id,
                'name'         => $request->input('name'),
                'url'          => $request->input('url'),
                'events'       => $request->input('events'),
                'secret'       => $secret,
                'is_active'    => $request->input('is_active', TRUE),
                'retry_policy' => $request->input('retry_policy', [
                    'max_attempts'     => 3,
                    'backoff_strategy' => 'exponential',
                ]),
                'custom_headers' => $request->input('headers', []),
                'timeout'        => $request->input('timeout', 10),
            ]);

            // Test webhook if requested
            $testResult = NULL;
            if ($request->boolean('test_on_create', FALSE)) {
                $testResult = $this->performWebhookTest($webhook);
            }

            return $this->successResponse([
                'message' => 'Webhook created successfully',
                'webhook' => [
                    'id'         => $webhook->id,
                    'name'       => $webhook->name,
                    'url'        => $webhook->url,
                    'events'     => $webhook->events,
                    'secret'     => $webhook->secret,
                    'is_active'  => $webhook->is_active,
                    'created_at' => $webhook->created_at,
                ],
                'test_result' => $testResult,
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create webhook', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get webhook details
     */
    public function show(Webhook $webhook): JsonResponse
    {
        // Check ownership
        if ($webhook->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access to webhook', [], 403);
        }

        try {
            $webhook->load(['logs' => function ($query) {
                $query->latest()->limit(20);
            }]);

            return $this->successResponse([
                'webhook' => [
                    'id'             => $webhook->id,
                    'name'           => $webhook->name,
                    'url'            => $webhook->url,
                    'events'         => $webhook->events,
                    'is_active'      => $webhook->is_active,
                    'secret'         => $webhook->secret,
                    'retry_policy'   => $webhook->retry_policy,
                    'custom_headers' => $webhook->custom_headers,
                    'timeout'        => $webhook->timeout,
                    'statistics'     => [
                        'total_deliveries'         => $webhook->total_deliveries,
                        'successful_deliveries'    => $webhook->successful_deliveries,
                        'failed_deliveries'        => $webhook->failed_deliveries,
                        'success_rate'             => $webhook->getSuccessRate(),
                        'avg_response_time'        => $webhook->getAverageResponseTime(),
                        'last_delivery'            => $webhook->last_delivery_at,
                        'last_successful_delivery' => $webhook->last_successful_delivery_at,
                        'status'                   => $webhook->getStatus(),
                    ],
                    'recent_logs' => $webhook->logs->map(function ($log) {
                        return [
                            'id'             => $log->id,
                            'event_type'     => $log->event_type,
                            'status'         => $log->status,
                            'response_code'  => $log->response_code,
                            'response_time'  => $log->response_time,
                            'error_message'  => $log->error_message,
                            'attempt_number' => $log->attempt_number,
                            'delivered_at'   => $log->delivered_at,
                        ];
                    }),
                    'created_at' => $webhook->created_at,
                    'updated_at' => $webhook->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve webhook details', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update webhook
     */
    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        // Check ownership
        if ($webhook->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access to webhook', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'         => 'string|max:255',
            'url'          => 'url|max:255',
            'events'       => 'array',
            'events.*'     => 'string|in:price_alert,monitoring_update,purchase_complete,system_notification,ticket_available,price_drop',
            'secret'       => 'nullable|string|min:16|max:255',
            'is_active'    => 'boolean',
            'retry_policy' => 'array',
            'headers'      => 'array',
            'timeout'      => 'integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $updateData = $request->only([
                'name', 'url', 'events', 'secret', 'is_active',
                'retry_policy', 'custom_headers', 'timeout',
            ]);

            // Filter out null values
            $updateData = array_filter($updateData, function ($value) {
                return $value !== NULL;
            });

            $webhook->update($updateData);

            return $this->successResponse([
                'message' => 'Webhook updated successfully',
                'webhook' => [
                    'id'         => $webhook->id,
                    'name'       => $webhook->name,
                    'url'        => $webhook->url,
                    'events'     => $webhook->events,
                    'is_active'  => $webhook->is_active,
                    'updated_at' => $webhook->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update webhook', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete webhook
     */
    public function delete(Webhook $webhook): JsonResponse
    {
        // Check ownership
        if ($webhook->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access to webhook', [], 403);
        }

        try {
            // Soft delete to preserve logs
            $webhook->delete();

            return $this->successResponse([
                'message' => 'Webhook deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete webhook', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function test(Request $request, Webhook $webhook): JsonResponse
    {
        // Check ownership
        if ($webhook->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access to webhook', [], 403);
        }

        try {
            $testResult = $this->performWebhookTest($webhook, $request->input('event_type', 'test'));

            return $this->successResponse([
                'test_result' => $testResult,
                'message'     => $testResult['success'] ? 'Webhook test successful' : 'Webhook test failed',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to test webhook', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get webhook delivery logs
     */
    public function logs(Request $request, Webhook $webhook): JsonResponse
    {
        // Check ownership
        if ($webhook->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access to webhook', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'status'     => 'string|in:success,failed,pending',
            'event_type' => 'string',
            'from_date'  => 'date',
            'to_date'    => 'date|after_or_equal:from_date',
            'per_page'   => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $query = $webhook->logs();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('event_type')) {
                $query->where('event_type', $request->input('event_type'));
            }

            if ($request->filled('from_date')) {
                $query->where('delivered_at', '>=', $request->input('from_date'));
            }

            if ($request->filled('to_date')) {
                $query->where('delivered_at', '<=', $request->input('to_date'));
            }

            $perPage = min($request->input('per_page', 20), 100);
            $logs = $query->orderByDesc('delivered_at')->paginate($perPage);

            return $this->successResponse([
                'logs'       => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'total_pages'  => $logs->lastPage(),
                    'total_items'  => $logs->total(),
                    'per_page'     => $logs->perPage(),
                ],
                'summary' => [
                    'total_deliveries'  => $webhook->total_deliveries,
                    'success_rate'      => $webhook->getSuccessRate(),
                    'avg_response_time' => $webhook->getAverageResponseTime(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve webhook logs', ['error' => $e->getMessage()]);
        }
    }

    // Webhook receiver endpoints (for incoming webhooks)

    /**
     * Receive price alert webhook
     */
    public function receivePriceAlert(Request $request): JsonResponse
    {
        return $this->processIncomingWebhook($request, 'price_alert');
    }

    /**
     * Receive monitoring update webhook
     */
    public function receiveMonitoringUpdate(Request $request): JsonResponse
    {
        return $this->processIncomingWebhook($request, 'monitoring_update');
    }

    /**
     * Receive purchase complete webhook
     */
    public function receivePurchaseComplete(Request $request): JsonResponse
    {
        return $this->processIncomingWebhook($request, 'purchase_complete');
    }

    /**
     * Receive system notification webhook
     */
    public function receiveSystemNotification(Request $request): JsonResponse
    {
        return $this->processIncomingWebhook($request, 'system_notification');
    }

    // Protected helper methods

    protected function successResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success'   => TRUE,
            'data'      => $data,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    protected function errorResponse(string $message, array $errors = [], int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success'   => FALSE,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    private function getMaxWebhooks($user): int
    {
        return match ($user->subscription_plan) {
            'starter'    => 1,
            'pro'        => 5,
            'enterprise' => 25,
            default      => 0
        };
    }

    private function performWebhookTest(Webhook $webhook, string $eventType = 'test'): array
    {
        $testPayload = [
            'event_type' => $eventType,
            'test'       => TRUE,
            'webhook_id' => $webhook->id,
            'timestamp'  => now()->toISOString(),
            'data'       => [
                'message' => 'This is a test webhook delivery',
                'test_id' => Str::uuid(),
            ],
        ];

        try {
            $startTime = microtime(TRUE);

            $response = Http::withHeaders($webhook->custom_headers ?? [])
                ->timeout($webhook->timeout)
                ->post($webhook->url, [
                    'payload'   => $testPayload,
                    'signature' => $this->generateSignature($testPayload, $webhook->secret),
                ]);

            $responseTime = round((microtime(TRUE) - $startTime) * 1000);

            // Log the test
            WebhookLog::create([
                'webhook_id'     => $webhook->id,
                'event_type'     => $eventType,
                'payload'        => $testPayload,
                'status'         => $response->successful() ? 'success' : 'failed',
                'response_code'  => $response->status(),
                'response_body'  => $response->body(),
                'response_time'  => $responseTime,
                'attempt_number' => 1,
                'delivered_at'   => now(),
            ]);

            return [
                'success'       => $response->successful(),
                'status_code'   => $response->status(),
                'response_time' => $responseTime,
                'response_body' => $response->body(),
                'test_payload'  => $testPayload,
            ];
        } catch (\Exception $e) {
            // Log the failed test
            WebhookLog::create([
                'webhook_id'     => $webhook->id,
                'event_type'     => $eventType,
                'payload'        => $testPayload,
                'status'         => 'failed',
                'error_message'  => $e->getMessage(),
                'attempt_number' => 1,
                'delivered_at'   => now(),
            ]);

            return [
                'success'      => FALSE,
                'error'        => $e->getMessage(),
                'test_payload' => $testPayload,
            ];
        }
    }

    private function generateSignature(array $payload, string $secret): string
    {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    private function processIncomingWebhook(Request $request, string $eventType): JsonResponse
    {
        try {
            // Validate incoming webhook signature if needed
            $signature = $request->header('X-Signature');

            // Process the webhook data
            $data = $request->all();

            // Log receipt for debugging/analytics
            \Log::info("Received {$eventType} webhook", [
                'data'      => $data,
                'signature' => $signature,
                'ip'        => $request->ip(),
            ]);

            return response()->json([
                'success'     => TRUE,
                'message'     => 'Webhook received successfully',
                'event_type'  => $eventType,
                'received_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to process webhook',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
