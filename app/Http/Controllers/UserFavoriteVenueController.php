<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserFavoriteVenue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use function strlen;

class UserFavoriteVenueController extends Controller
{
    /**
     * Display the user's favorite venues
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $query = UserFavoriteVenue::where('user_id', $user->id);

        // Apply filters
        if ($request->city) {
            $query->byCity($request->city);
        }

        if ($request->state_province) {
            $query->byStateProvince($request->state_province);
        }

        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->venue_type) {
            $query->byVenueType($request->venue_type);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $venues = $query->orderBy('priority', 'desc')
            ->orderBy('venue_name')
            ->paginate(20);

        $stats = UserFavoriteVenue::getVenueStats($user->id);
        $venueTypes = UserFavoriteVenue::getAvailableVenueTypes();

        if ($request->wantsJson()) {
            return response()->json([
                'venues'      => $venues,
                'stats'       => $stats,
                'venue_types' => $venueTypes,
            ]);
        }

        return view('preferences.venues.index', compact(
            'venues',
            'stats',
            'venueTypes',
        ));
    }

    /**
     * Show the form for creating a new favorite venue
     */
    public function create(): View
    {
        $venueTypes = UserFavoriteVenue::getAvailableVenueTypes();
        $popularVenues = UserFavoriteVenue::getPopularVenues();

        return view('preferences.venues.create', compact(
            'venueTypes',
            'popularVenues',
        ));
    }

    /**
     * Store a newly created favorite venue
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'venue_name'      => 'required|string|max:255',
            'city'            => 'required|string|max:255',
            'state_province'  => 'nullable|string|max:100',
            'country'         => 'required|string|max:100',
            'capacity'        => 'nullable|integer|min:1',
            'venue_types'     => 'required|array',
            'venue_types.*'   => 'string|in:' . implode(',', array_keys(UserFavoriteVenue::getAvailableVenueTypes())),
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'venue_image_url' => 'nullable|url',
            'aliases'         => 'nullable|array',
            'aliases.*'       => 'string|max:255',
            'email_alerts'    => 'boolean',
            'push_alerts'     => 'boolean',
            'sms_alerts'      => 'boolean',
            'priority'        => 'integer|min:1|max:5',
        ]);

        // Check for duplicate venue
        $existing = UserFavoriteVenue::where('user_id', $user->id)
            ->where('venue_name', $validated['venue_name'])
            ->where('city', $validated['city'])
            ->first();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'This venue is already in your favorites',
                ], 422);
            }

            return back()->withErrors(['venue_name' => 'This venue is already in your favorites']);
        }

        $validated['user_id'] = $user->id;
        $validated['email_alerts'] = $request->boolean('email_alerts', TRUE);
        $validated['push_alerts'] = $request->boolean('push_alerts', FALSE);
        $validated['sms_alerts'] = $request->boolean('sms_alerts', FALSE);
        $validated['priority'] ??= 3;

        $venue = UserFavoriteVenue::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'venue'   => $venue,
                'message' => 'Venue added to favorites successfully!',
            ], 201);
        }

        return redirect()->route('preferences.venues.index')
            ->with('success', 'Venue added to favorites successfully!');
    }

    /**
     * Display the specified favorite venue
     */
    public function show(UserFavoriteVenue $venue): View|JsonResponse
    {
        $this->authorize('view', $venue);

        if (request()->wantsJson()) {
            return response()->json(['venue' => $venue]);
        }

        return view('preferences.venues.show', compact('venue'));
    }

    /**
     * Show the form for editing the specified favorite venue
     */
    public function edit(UserFavoriteVenue $venue): View
    {
        $this->authorize('update', $venue);

        $venueTypes = UserFavoriteVenue::getAvailableVenueTypes();

        return view('preferences.venues.edit', compact(
            'venue',
            'venueTypes',
        ));
    }

    /**
     * Update the specified favorite venue
     */
    public function update(Request $request, UserFavoriteVenue $venue): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $venue);

        $validated = $request->validate([
            'venue_name'      => 'required|string|max:255',
            'city'            => 'required|string|max:255',
            'state_province'  => 'nullable|string|max:100',
            'country'         => 'required|string|max:100',
            'capacity'        => 'nullable|integer|min:1',
            'venue_types'     => 'required|array',
            'venue_types.*'   => 'string|in:' . implode(',', array_keys(UserFavoriteVenue::getAvailableVenueTypes())),
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'venue_image_url' => 'nullable|url',
            'aliases'         => 'nullable|array',
            'aliases.*'       => 'string|max:255',
            'email_alerts'    => 'boolean',
            'push_alerts'     => 'boolean',
            'sms_alerts'      => 'boolean',
            'priority'        => 'integer|min:1|max:5',
        ]);

        $validated['email_alerts'] = $request->boolean('email_alerts');
        $validated['push_alerts'] = $request->boolean('push_alerts');
        $validated['sms_alerts'] = $request->boolean('sms_alerts');

        $venue->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'venue'   => $venue->fresh(),
                'message' => 'Venue preferences updated successfully!',
            ]);
        }

        return redirect()->route('preferences.venues.index')
            ->with('success', 'Venue preferences updated successfully!');
    }

    /**
     * Remove the specified favorite venue
     */
    public function destroy(UserFavoriteVenue $venue): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $venue);

        $venueName = $venue->full_name;
        $venue->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => "Removed {$venueName} from favorites",
            ]);
        }

        return redirect()->route('preferences.venues.index')
            ->with('success', "Removed {$venueName} from favorites");
    }

    /**
     * Update notification settings for a venue
     */
    public function updateNotifications(Request $request, UserFavoriteVenue $venue): JsonResponse
    {
        $this->authorize('update', $venue);

        $validated = $request->validate([
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
        ]);

        $venue->updateNotificationSettings($validated);

        return response()->json([
            'message'  => 'Notification settings updated',
            'settings' => $venue->getNotificationSettings(),
        ]);
    }

    /**
     * Search for venues (autocomplete API)
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q', '');
        $city = $request->get('city');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $popularVenues = UserFavoriteVenue::getPopularVenues($city);

        $results = collect($popularVenues)
            ->filter(function ($venue) use ($term) {
                return str_contains(strtolower($venue['full_name']), strtolower($term))
                       || str_contains(strtolower($venue['name']), strtolower($term))
                       || str_contains(strtolower($venue['city'] ?? ''), strtolower($term));
            })
            ->take(10)
            ->values();

        return response()->json($results);
    }

    /**
     * Find venues near user's location
     */
    public function nearMe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius'    => 'integer|min:1|max:500',
        ]);

        $user = Auth::user();
        $radius = $validated['radius'] ?? 50; // Default 50 miles

        $venues = UserFavoriteVenue::where('user_id', $user->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withinRadius(
                $validated['latitude'],
                $validated['longitude'],
                $radius,
            )
            ->get()
            ->map(function ($venue) use ($validated) {
                $venue->distance = $venue->distanceFrom(
                    $validated['latitude'],
                    $validated['longitude'],
                );

                return $venue;
            })
            ->sortBy('distance');

        return response()->json([
            'venues' => $venues,
            'center' => [
                'latitude'  => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            'radius'      => $radius,
            'total_found' => $venues->count(),
        ]);
    }

    /**
     * Import venues from a list or CSV
     */
    public function import(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'venues'                  => 'required|array',
            'venues.*.venue_name'     => 'required|string',
            'venues.*.city'           => 'required|string',
            'venues.*.state_province' => 'nullable|string',
            'venues.*.country'        => 'required|string',
            'venues.*.venue_types'    => 'nullable|array',
            'default_priority'        => 'integer|min:1|max:5',
            'default_email_alerts'    => 'boolean',
        ]);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($request->venues as $venueData) {
            try {
                // Check if venue already exists
                $existing = UserFavoriteVenue::where('user_id', $user->id)
                    ->where('venue_name', $venueData['venue_name'])
                    ->where('city', $venueData['city'])
                    ->exists();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                UserFavoriteVenue::create([
                    'user_id'        => $user->id,
                    'venue_name'     => $venueData['venue_name'],
                    'city'           => $venueData['city'],
                    'state_province' => $venueData['state_province'] ?? NULL,
                    'country'        => $venueData['country'],
                    'venue_types'    => $venueData['venue_types'] ?? ['other'],
                    'priority'       => $request->get('default_priority', 3),
                    'email_alerts'   => $request->boolean('default_email_alerts', TRUE),
                    'push_alerts'    => FALSE,
                    'sms_alerts'     => FALSE,
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import {$venueData['venue_name']}: " . $e->getMessage();
            }
        }

        $message = "Import completed. {$imported} venues added";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (already exists)";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => $message,
                'imported' => $imported,
                'skipped'  => $skipped,
                'errors'   => $errors,
            ]);
        }

        $redirectResponse = redirect()->route('preferences.venues.index')
            ->with('success', $message);

        if (! empty($errors)) {
            $redirectResponse->with('errors', $errors);
        }

        return $redirectResponse;
    }

    /**
     * Export user's favorite venues
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $venues = UserFavoriteVenue::where('user_id', $user->id)
            ->orderBy('city')
            ->orderBy('venue_name')
            ->get();

        $format = $request->get('format', 'json');

        if ($format === 'csv') {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="favorite_venues.csv"',
            ];

            $callback = function () use ($venues): void {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Venue Name', 'City', 'State/Province', 'Country', 'Venue Types', 'Capacity', 'Priority', 'Email Alerts', 'Push Alerts', 'SMS Alerts']);

                foreach ($venues as $venue) {
                    fputcsv($file, [
                        $venue->venue_name,
                        $venue->city,
                        $venue->state_province,
                        $venue->country,
                        implode(', ', $venue->venue_types ?? []),
                        $venue->capacity,
                        $venue->priority,
                        $venue->email_alerts ? 'Yes' : 'No',
                        $venue->push_alerts ? 'Yes' : 'No',
                        $venue->sms_alerts ? 'Yes' : 'No',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Default JSON export
        return response()->json([
            'venues'      => $venues,
            'exported_at' => now()->toISOString(),
            'total_count' => $venues->count(),
        ]);
    }

    /**
     * Bulk update venues (priority, notifications, etc.)
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'venue_ids'    => 'required|array',
            'venue_ids.*'  => 'exists:user_favorite_venues,id',
            'action'       => 'required|in:update_priority,update_notifications,delete',
            'priority'     => 'required_if:action,update_priority|integer|min:1|max:5',
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
        ]);

        $venues = UserFavoriteVenue::whereIn('id', $validated['venue_ids'])
            ->where('user_id', $user->id)
            ->get();

        $updated = 0;

        foreach ($venues as $venue) {
            switch ($validated['action']) {
                case 'update_priority':
                    $venue->update(['priority' => $validated['priority']]);
                    $updated++;

                    break;
                case 'update_notifications':
                    $venue->updateNotificationSettings([
                        'email' => $request->boolean('email_alerts'),
                        'push'  => $request->boolean('push_alerts'),
                        'sms'   => $request->boolean('sms_alerts'),
                    ]);
                    $updated++;

                    break;
                case 'delete':
                    $venue->delete();
                    $updated++;

                    break;
            }
        }

        return response()->json([
            'message'       => "Updated {$updated} venues",
            'updated_count' => $updated,
        ]);
    }

    /**
     * Get similar venues based on current venue
     */
    public function getSimilar(UserFavoriteVenue $venue): JsonResponse
    {
        $this->authorize('view', $venue);

        $similarVenues = $venue->getSimilarVenues(10);

        return response()->json([
            'similar_venues' => $similarVenues,
            'base_venue'     => [
                'id'   => $venue->id,
                'name' => $venue->venue_name,
                'city' => $venue->city,
            ],
        ]);
    }
}
