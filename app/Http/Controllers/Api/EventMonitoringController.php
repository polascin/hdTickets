<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\EventStore\EventStoreInterface;
use App\Infrastructure\Projections\ProjectionManagerInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function array_slice;
use function count;
use function is_string;
use function strlen;

class EventMonitoringController extends Controller
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly ProjectionManagerInterface $projectionManager,
    ) {
    }

    /**
     * Get event store overview
     */
    /**
     * Overview
     */
    public function overview(): JsonResponse
    {
        $data = Cache::remember('event_monitoring_overview', 60, function () {
            return [
                'total_events' => DB::table('event_store')->count(),
                'events_today' => DB::table('event_store')
                    ->whereDate('recorded_at', today())
                    ->count(),
                'active_projections' => count($this->projectionManager->getProjections()),
                'failed_processing'  => DB::table('event_processing_failures')
                    ->where('is_resolved', FALSE)
                    ->count(),
                'event_types' => DB::table('event_store')
                    ->select('event_type', DB::raw('count(*) as count'))
                    ->groupBy('event_type')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'type'  => class_basename($item->event_type),
                            'count' => $item->count,
                        ];
                    }),
                'recent_activity' => DB::table('event_store')
                    ->select(DB::raw('DATE(recorded_at) as date'), DB::raw('count(*) as count'))
                    ->where('recorded_at', '>=', now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'date'  => $item->date,
                            'count' => $item->count,
                        ];
                    }),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get projection status
     */
    /**
     * Projections
     */
    public function projections(): JsonResponse
    {
        $projections = $this->projectionManager->getProjections();
        $data = [];

        foreach ($projections as $projectionName) {
            $status = $this->projectionManager->getProjectionStatus($projectionName);
            $data[] = [
                'name'           => $projectionName,
                'position'       => $status['position'],
                'last_updated'   => $status['last_updated_at'],
                'is_locked'      => $status['is_locked'],
                'locked_by'      => $status['locked_by'],
                'state'          => $status['state'],
                'handled_events' => count($status['handled_event_types']),
            ];
        }

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get processing failures
     */
    /**
     * Failures
     */
    public function failures(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $resolved = $request->get('resolved', FALSE);

        $query = DB::table('event_processing_failures')
            ->orderBy('failed_at', 'desc');

        if (! $resolved) {
            $query->where('is_resolved', FALSE);
        }

        $total = $query->count();
        $failures = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = $failures->map(function ($failure) {
            return [
                'id'                => $failure->id,
                'event_id'          => $failure->event_id,
                'subscription_name' => $failure->subscription_name,
                'handler_class'     => class_basename($failure->handler_class),
                'error_type'        => class_basename($failure->error_type),
                'error_message'     => $failure->error_message,
                'retry_count'       => $failure->retry_count,
                'failed_at'         => $failure->failed_at,
                'retry_after'       => $failure->retry_after,
                'is_resolved'       => $failure->is_resolved,
                'resolved_at'       => $failure->resolved_at,
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => [
                'failures'   => $data,
                'pagination' => [
                    'page'  => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ],
        ]);
    }

    /**
     * Get recent events
     */
    /**
     * RecentEvents
     */
    public function recentEvents(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $eventType = $request->get('event_type');
        $aggregateType = $request->get('aggregate_type');

        $query = DB::table('event_store')
            ->orderBy('recorded_at', 'desc')
            ->limit($limit);

        if ($eventType) {
            $query->where('event_type', 'like', "%{$eventType}%");
        }

        if ($aggregateType) {
            $query->where('aggregate_type', $aggregateType);
        }

        $events = $query->get()->map(function ($event) {
            return [
                'id'                => $event->id,
                'event_id'          => $event->event_id,
                'event_type'        => class_basename($event->event_type),
                'aggregate_type'    => $event->aggregate_type,
                'aggregate_root_id' => $event->aggregate_root_id,
                'aggregate_version' => $event->aggregate_version,
                'recorded_at'       => $event->recorded_at,
                'payload_preview'   => $this->getPayloadPreview(json_decode($event->payload, TRUE)),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $events,
        ]);
    }

    /**
     * Get event statistics
     */
    /**
     * Statistics
     */
    public function statistics(): JsonResponse
    {
        $data = Cache::remember('event_statistics', 300, function () {
            return [
                'events_by_type' => DB::table('event_store')
                    ->select('event_type', DB::raw('count(*) as count'))
                    ->groupBy('event_type')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'type'      => class_basename($item->event_type),
                            'full_type' => $item->event_type,
                            'count'     => $item->count,
                        ];
                    }),
                'events_by_aggregate' => DB::table('event_store')
                    ->select('aggregate_type', DB::raw('count(*) as count'))
                    ->groupBy('aggregate_type')
                    ->orderBy('count', 'desc')
                    ->get(),
                'hourly_activity' => DB::table('event_store')
                    ->select(DB::raw('HOUR(recorded_at) as hour'), DB::raw('count(*) as count'))
                    ->whereDate('recorded_at', today())
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get(),
                'daily_activity' => DB::table('event_store')
                    ->select(DB::raw('DATE(recorded_at) as date'), DB::raw('count(*) as count'))
                    ->where('recorded_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Rebuild projection
     */
    /**
     * RebuildProjection
     */
    public function rebuildProjection(Request $request, string $projectionName): JsonResponse
    {
        try {
            $fromPosition = $request->get('from_position', 0);

            $this->projectionManager->rebuild($projectionName, $fromPosition);

            return response()->json([
                'success' => TRUE,
                'message' => "Projection '{$projectionName}' rebuilt successfully",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resolve processing failure
     */
    /**
     * ResolveFailure
     */
    public function resolveFailure(int $failureId): JsonResponse
    {
        try {
            DB::table('event_processing_failures')
                ->where('id', $failureId)
                ->update([
                    'is_resolved' => TRUE,
                    'resolved_at' => now(),
                ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Processing failure resolved',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'error'   => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get  payload preview
     */
    private function getPayloadPreview(array $payload): string
    {
        // Create a brief preview of the payload
        $preview = [];

        foreach (array_slice($payload, 0, 3, TRUE) as $key => $value) {
            if (is_string($value) && strlen($value) > 30) {
                $value = substr($value, 0, 30) . '...';
            }
            $preview[$key] = $value;
        }

        return json_encode($preview, JSON_UNESCAPED_SLASHES);
    }
}
