<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TicketCriteriaController extends Controller
{
    /**
     * Get all ticket criteria for user
     */
    /**
     * Index
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $cacheKey = "ticket_criteria_{$userId}";

        $criteria = Cache::remember($cacheKey, 1800, function () {
            return collect([
                [
                    'id'                    => 1,
                    'name'                  => 'Lakers Home Games',
                    'description'           => 'Lakers games at Crypto.com Arena',
                    'is_active'             => TRUE,
                    'platforms'             => ['ticketmaster', 'stubhub', 'seatgeek'],
                    'keywords'              => ['Lakers', 'Los Angeles Lakers'],
                    'venue_keywords'        => ['Crypto.com Arena', 'Staples Center'],
                    'price_range'           => ['min' => 100, 'max' => 500],
                    'section_preferences'   => ['Lower Bowl', 'Club Level'],
                    'exclude_keywords'      => ['parking', 'merchandise'],
                    'notification_settings' => [
                        'email'                => TRUE,
                        'sms'                  => FALSE,
                        'push'                 => TRUE,
                        'price_drop_threshold' => 15,
                    ],
                    'created_at' => now()->subDays(5)->toISOString(),
                    'updated_at' => now()->subHours(2)->toISOString(),
                ],
                [
                    'id'                    => 2,
                    'name'                  => 'NFL Playoff Games',
                    'description'           => 'Any NFL playoff games nationwide',
                    'is_active'             => TRUE,
                    'platforms'             => ['ticketmaster', 'stubhub', 'viagogo'],
                    'keywords'              => ['NFL', 'Playoff', 'Championship'],
                    'venue_keywords'        => [],
                    'price_range'           => ['min' => 200, 'max' => 1200],
                    'section_preferences'   => ['Lower Level', 'Club'],
                    'exclude_keywords'      => ['standing room', 'obstructed view'],
                    'notification_settings' => [
                        'email'                => TRUE,
                        'sms'                  => TRUE,
                        'push'                 => TRUE,
                        'price_drop_threshold' => 20,
                    ],
                    'created_at' => now()->subDays(10)->toISOString(),
                    'updated_at' => now()->subDays(1)->toISOString(),
                ],
            ]);
        });

        return response()->json([
            'data'   => $criteria,
            'total'  => $criteria->count(),
            'active' => $criteria->where('is_active', TRUE)->count(),
        ]);
    }

    /**
     * Store new ticket criteria
     */
    /**
     * Store
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                                       => 'required|string|max:255',
            'description'                                => 'nullable|string|max:500',
            'platforms'                                  => 'required|array|min:1',
            'platforms.*'                                => 'string|in:ticketmaster,stubhub,viagogo,seatgeek,tickpick',
            'keywords'                                   => 'required|array|min:1',
            'keywords.*'                                 => 'string|max:100',
            'venue_keywords'                             => 'nullable|array',
            'venue_keywords.*'                           => 'string|max:100',
            'price_range'                                => 'required|array',
            'price_range.min'                            => 'required|numeric|min:0',
            'price_range.max'                            => 'required|numeric|gt:price_range.min',
            'section_preferences'                        => 'nullable|array',
            'section_preferences.*'                      => 'string|max:100',
            'exclude_keywords'                           => 'nullable|array',
            'exclude_keywords.*'                         => 'string|max:100',
            'notification_settings'                      => 'required|array',
            'notification_settings.email'                => 'boolean',
            'notification_settings.sms'                  => 'boolean',
            'notification_settings.push'                 => 'boolean',
            'notification_settings.price_drop_threshold' => 'numeric|min:0|max:100',
        ]);

        $userId = auth()->id();
        $cacheKey = "ticket_criteria_{$userId}";

        $criteria = Cache::get($cacheKey, collect());

        $newCriteria = array_merge($validated, [
            'id'         => $criteria->max('id') + 1,
            'is_active'  => TRUE,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ]);

        $criteria->push($newCriteria);
        Cache::put($cacheKey, $criteria, 1800);

        return response()->json([
            'message' => 'Ticket criteria created successfully',
            'data'    => $newCriteria,
        ], 201);
    }

    /**
     * Update ticket criteria
     */
    /**
     * Update
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'                                       => 'sometimes|string|max:255',
            'description'                                => 'nullable|string|max:500',
            'platforms'                                  => 'sometimes|array|min:1',
            'platforms.*'                                => 'string|in:ticketmaster,stubhub,viagogo,seatgeek,tickpick',
            'keywords'                                   => 'sometimes|array|min:1',
            'keywords.*'                                 => 'string|max:100',
            'venue_keywords'                             => 'nullable|array',
            'venue_keywords.*'                           => 'string|max:100',
            'price_range'                                => 'sometimes|array',
            'price_range.min'                            => 'required_with:price_range|numeric|min:0',
            'price_range.max'                            => 'required_with:price_range|numeric|gt:price_range.min',
            'section_preferences'                        => 'nullable|array',
            'section_preferences.*'                      => 'string|max:100',
            'exclude_keywords'                           => 'nullable|array',
            'exclude_keywords.*'                         => 'string|max:100',
            'notification_settings'                      => 'sometimes|array',
            'notification_settings.email'                => 'boolean',
            'notification_settings.sms'                  => 'boolean',
            'notification_settings.push'                 => 'boolean',
            'notification_settings.price_drop_threshold' => 'numeric|min:0|max:100',
        ]);

        $userId = auth()->id();
        $cacheKey = "ticket_criteria_{$userId}";

        $criteria = Cache::get($cacheKey, collect());
        $criteriaIndex = $criteria->search(fn ($item) => $item['id'] === $id);

        if ($criteriaIndex === FALSE) {
            return response()->json(['message' => 'Ticket criteria not found'], 404);
        }

        $existingCriteria = $criteria[$criteriaIndex];
        $updatedCriteria = array_merge($existingCriteria, $validated, [
            'updated_at' => now()->toISOString(),
        ]);

        $criteria[$criteriaIndex] = $updatedCriteria;
        Cache::put($cacheKey, $criteria, 1800);

        return response()->json([
            'message' => 'Ticket criteria updated successfully',
            'data'    => $updatedCriteria,
        ]);
    }

    /**
     * Delete ticket criteria
     */
    /**
     * Destroy
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = auth()->id();
        $cacheKey = "ticket_criteria_{$userId}";

        $criteria = Cache::get($cacheKey, collect());
        $criteriaIndex = $criteria->search(fn ($item) => $item['id'] === $id);

        if ($criteriaIndex === FALSE) {
            return response()->json(['message' => 'Ticket criteria not found'], 404);
        }

        $criteria->forget($criteriaIndex);
        Cache::put($cacheKey, $criteria->values(), 1800);

        return response()->json([
            'message' => 'Ticket criteria deleted successfully',
        ]);
    }

    /**
     * Toggle ticket criteria active status
     */
    /**
     * Toggle
     */
    public function toggle(int $id): JsonResponse
    {
        $userId = auth()->id();
        $cacheKey = "ticket_criteria_{$userId}";

        $criteria = Cache::get($cacheKey, collect());
        $criteriaIndex = $criteria->search(fn ($item) => $item['id'] === $id);

        if ($criteriaIndex === FALSE) {
            return response()->json(['message' => 'Ticket criteria not found'], 404);
        }

        $existingCriteria = $criteria[$criteriaIndex];
        $existingCriteria['is_active'] = ! $existingCriteria['is_active'];
        $existingCriteria['updated_at'] = now()->toISOString();

        $criteria[$criteriaIndex] = $existingCriteria;
        Cache::put($cacheKey, $criteria, 1800);

        return response()->json([
            'message' => 'Ticket criteria status toggled successfully',
            'data'    => $existingCriteria,
        ]);
    }
}
