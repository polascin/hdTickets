<?php

use App\Http\Controllers\Api\NotificationPreferencesController;
use App\Http\Controllers\Api\NotificationChannelsController;
use App\Http\Controllers\Api\EnhancedAlertsController;
use App\Http\Controllers\Api\AlertAnalyticsController;
use App\Http\Controllers\Api\AdvancedAnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Enhanced Alert System API Routes
|--------------------------------------------------------------------------
|
| API routes for the enhanced alert system including preferences,
| channels, analytics, and testing endpoints.
|
*/

Route::middleware(['auth:sanctum', 'track.user.activity'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Notification Preferences
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications/preferences')->name('api.notifications.preferences.')->group(function () {
        Route::get('/', [NotificationPreferencesController::class, 'index'])->name('index');
        Route::put('/', [NotificationPreferencesController::class, 'update'])->name('update');
        Route::post('/reset', [NotificationPreferencesController::class, 'reset'])->name('reset');
        Route::get('/export', [NotificationPreferencesController::class, 'export'])->name('export');
        Route::post('/import', [NotificationPreferencesController::class, 'import'])->name('import');
        
        // Individual preference management
        Route::get('/{key}', [NotificationPreferencesController::class, 'show'])->name('show');
        Route::put('/{key}', [NotificationPreferencesController::class, 'updatePreference'])->name('update-preference');
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications/channels')->name('api.notifications.channels.')->group(function () {
        Route::get('/', [NotificationChannelsController::class, 'index'])->name('index');
        Route::post('/', [NotificationChannelsController::class, 'store'])->name('store');
        Route::get('/supported', [NotificationChannelsController::class, 'supported'])->name('supported');
        
        Route::prefix('{channel}')->group(function () {
            Route::get('/', [NotificationChannelsController::class, 'show'])->name('show');
            Route::put('/', [NotificationChannelsController::class, 'update'])->name('update');
            Route::delete('/', [NotificationChannelsController::class, 'destroy'])->name('destroy');
            Route::post('/test', [NotificationChannelsController::class, 'test'])->name('test');
            Route::post('/toggle', [NotificationChannelsController::class, 'toggle'])->name('toggle');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Enhanced Alerts Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('enhanced-alerts')->name('api.enhanced-alerts.')->group(function () {
        
        // Alert status and management
        Route::get('/status', [EnhancedAlertsController::class, 'status'])->name('status');
        Route::get('/escalations', [EnhancedAlertsController::class, 'escalations'])->name('escalations');
        Route::post('/escalations/{escalation}/cancel', [EnhancedAlertsController::class, 'cancelEscalation'])->name('cancel-escalation');
        
        // ML Predictions
        Route::get('/predictions/{ticket}', [EnhancedAlertsController::class, 'getPredictions'])->name('predictions');
        Route::post('/feedback/prediction', [EnhancedAlertsController::class, 'submitPredictionFeedback'])->name('prediction-feedback');
        
        // Alert acknowledgment
        Route::post('/acknowledge/{alert}', [EnhancedAlertsController::class, 'acknowledgeAlert'])->name('acknowledge');
        Route::post('/snooze/{alert}', [EnhancedAlertsController::class, 'snoozeAlert'])->name('snooze');
        
        // System health
        Route::get('/health', [EnhancedAlertsController::class, 'health'])->name('health');
    });

    /*
    |--------------------------------------------------------------------------
    | Analytics and Insights
    |--------------------------------------------------------------------------
    */
    Route::prefix('analytics')->name('api.analytics.')->group(function () {
        
        // User analytics
        Route::get('/alerts', [AlertAnalyticsController::class, 'alertAnalytics'])->name('alerts');
        Route::get('/channels', [AlertAnalyticsController::class, 'channelPerformance'])->name('channels');
        Route::get('/predictions', [AlertAnalyticsController::class, 'predictionAccuracy'])->name('predictions');
        Route::get('/engagement', [AlertAnalyticsController::class, 'userEngagement'])->name('engagement');
        
        // Advanced Analytics Dashboard
        Route::get('/price-trends', [AdvancedAnalyticsController::class, 'getPriceTrendAnalysis'])->name('price-trends');
        Route::get('/demand-patterns', [AdvancedAnalyticsController::class, 'getDemandPatternAnalysis'])->name('demand-patterns');
        Route::get('/success-optimization', [AdvancedAnalyticsController::class, 'getSuccessRateOptimization'])->name('success-optimization');
        Route::get('/platform-comparison', [AdvancedAnalyticsController::class, 'getPlatformPerformanceComparison'])->name('platform-comparison');
        Route::get('/real-time-metrics', [AdvancedAnalyticsController::class, 'getRealTimeDashboardMetrics'])->name('real-time-metrics');
        Route::get('/custom-dashboard', [AdvancedAnalyticsController::class, 'getCustomDashboard'])->name('custom-dashboard');
        Route::get('/export/{type}', [AdvancedAnalyticsController::class, 'exportAnalyticsData'])->name('export-data');
        
        // System analytics (admin only)
        Route::middleware('can:admin')->group(function () {
            Route::get('/system/overview', [AlertAnalyticsController::class, 'systemOverview'])->name('system.overview');
            Route::get('/system/performance', [AlertAnalyticsController::class, 'systemPerformance'])->name('system.performance');
            Route::get('/system/errors', [AlertAnalyticsController::class, 'errorAnalytics'])->name('system.errors');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | User Activity Tracking
    |--------------------------------------------------------------------------
    */
    Route::prefix('activity')->name('api.activity.')->group(function () {
        Route::get('/status', function () {
            return response()->json([
                'success' => true,
                'data' => \App\Http\Middleware\TrackUserActivity::getUserActivityStatus(auth()->id())
            ]);
        })->name('status');
        
        Route::post('/heartbeat', function () {
            \App\Http\Middleware\TrackUserActivity::markUserActive(auth()->id());
            return response()->json(['success' => true, 'timestamp' => now()->toISOString()]);
        })->name('heartbeat');
    });

});

/*
|--------------------------------------------------------------------------
| Public/Webhook Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->name('api.webhooks.')->group(function () {
    
    // Slack webhook handlers
    Route::post('/slack/events', [NotificationChannelsController::class, 'handleSlackEvent'])->name('slack.events');
    Route::post('/slack/interactive', [NotificationChannelsController::class, 'handleSlackInteractive'])->name('slack.interactive');
    
    // Discord webhook handlers
    Route::post('/discord/events', [NotificationChannelsController::class, 'handleDiscordEvent'])->name('discord.events');
    
    // Telegram webhook handlers
    Route::post('/telegram/webhook', [NotificationChannelsController::class, 'handleTelegramWebhook'])->name('telegram.webhook');
    
    // Generic webhook delivery confirmations
    Route::post('/delivery/confirm', [NotificationChannelsController::class, 'confirmDelivery'])->name('delivery.confirm');
});

/*
|--------------------------------------------------------------------------
| System Health and Monitoring (Internal)
|--------------------------------------------------------------------------
*/

Route::prefix('system')->name('api.system.')->middleware(['throttle:60,1'])->group(function () {
    
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'services' => [
                'enhanced_alerts' => config('notifications.enhanced_alerts.enabled', false),
                'ml_predictions' => config('notifications.ml.enabled', false),
                'escalation' => config('notifications.escalation.enabled', false),
                'channels' => [
                    'slack' => config('notifications.channels.slack.enabled', false),
                    'discord' => config('notifications.channels.discord.enabled', false),
                    'telegram' => config('notifications.channels.telegram.enabled', false),
                    'webhook' => config('notifications.channels.webhook.enabled', false),
                ]
            ]
        ]);
    })->name('health');
    
    Route::get('/metrics', function () {
        return response()->json([
            'queue_sizes' => [
                'critical' => \Illuminate\Support\Facades\Queue::size(config('notifications.queues.alerts.critical')),
                'high' => \Illuminate\Support\Facades\Queue::size(config('notifications.queues.alerts.high')),
                'medium' => \Illuminate\Support\Facades\Queue::size(config('notifications.queues.alerts.medium')),
                'default' => \Illuminate\Support\Facades\Queue::size(config('notifications.queues.alerts.default')),
            ],
            'cache_stats' => [
                'hit_ratio' => '95%', // Mock data - implement actual cache statistics
                'memory_usage' => '45MB'
            ],
            'timestamp' => now()->toISOString()
        ]);
    })->name('metrics');
});

/*
|--------------------------------------------------------------------------
| Development and Testing Routes (Only in non-production)
|--------------------------------------------------------------------------
*/

if (!app()->environment('production')) {
    Route::prefix('dev')->name('api.dev.')->middleware(['auth:sanctum'])->group(function () {
        
        // Test alert generation
        Route::post('/test-alert', function () {
            $user = auth()->user();
            
            // Generate test alert data
            $testAlertData = [
                'ticket' => [
                    'id' => 999,
                    'event_name' => 'Development Test Event',
                    'price' => 99.99,
                    'quantity' => 2,
                    'platform' => 'Test Platform',
                    'venue' => 'Test Venue',
                    'event_date' => now()->addDays(7)->toISOString()
                ],
                'alert' => ['id' => 999],
                'priority' => 4,
                'priority_label' => 'High',
                'prediction' => [
                    'price_trend' => 'increasing',
                    'availability_trend' => 'decreasing',
                    'demand_level' => 'high'
                ],
                'context' => [
                    'recommendation' => 'This is a development test alert'
                ],
                'actions' => [
                    'view_ticket' => url('/dev/test'),
                    'purchase_now' => url('/dev/test'),
                    'snooze_alert' => url('/dev/test')
                ]
            ];
            
            // Send test notification
            $user->notify(new \App\Notifications\SmartTicketAlert($testAlertData));
            
            return response()->json([
                'success' => true,
                'message' => 'Test alert sent',
                'data' => $testAlertData
            ]);
        })->name('test-alert');
        
        // Clear user activity cache
        Route::post('/clear-activity', function () {
            $userId = auth()->id();
            \Illuminate\Support\Facades\Cache::forget("user_activity:{$userId}");
            \Illuminate\Support\Facades\Cache::forget("user_last_activity:{$userId}");
            
            return response()->json(['success' => true, 'message' => 'Activity cache cleared']);
        })->name('clear-activity');
        
        // Generate sample ML predictions
        Route::get('/sample-predictions', function () {
            return response()->json([
                'success' => true,
                'data' => [
                    'availability_trend' => 'decreasing',
                    'availability_change' => -25,
                    'price_trend' => 'increasing',
                    'price_change' => 15,
                    'demand_level' => 'high',
                    'demand_score' => 0.85,
                    'confidence' => 0.92,
                    'recommendations' => [
                        [
                            'type' => 'urgency',
                            'message' => 'Tickets are selling fast. Purchase immediately to secure your spot.',
                            'priority' => 'high'
                        ]
                    ]
                ]
            ]);
        })->name('sample-predictions');
    });
}
