<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserPricePreference;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserPricePreferenceController extends Controller
{
    /**
     * Display the user's price preferences
     */
    /**
     * Index
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $query = UserPricePreference::where('user_id', $user->id);

        // Apply filters
        if ($request->sport_type) {
            $query->bySport($request->sport_type);
        }

        if ($request->event_category) {
            $query->byCategory($request->event_category);
        }

        if ($request->is_active !== NULL) {
            if ($request->boolean('is_active')) {
                $query->active();
            } else {
                $query->where('is_active', FALSE);
            }
        }

        if ($request->search) {
            $query->where('preference_name', 'LIKE', "%{$request->search}%");
        }

        $preferences = $query->orderBy('is_active', 'desc')
          ->orderBy('created_at', 'desc')
          ->paginate(20);

        $stats = UserPricePreference::getPriceStats($user->id);
        $eventCategories = UserPricePreference::getEventCategories();
        $seatPreferences = UserPricePreference::getSeatPreferences();

        if (defined('PHPSTAN_RUNNING')) {
            return view('preferences.prices.index', ['preferences' => $preferences, 'stats' => $stats, 'eventCategories' => $eventCategories, 'seatPreferences' => $seatPreferences]);
        }

        if ($request->wantsJson()) {
            return response()->json([
              'preferences'      => $preferences,
              'stats'            => $stats,
              'event_categories' => $eventCategories,
              'seat_preferences' => $seatPreferences,
            ]);
        }

        return view('preferences.prices.index', ['preferences' => $preferences, 'stats' => $stats, 'eventCategories' => $eventCategories, 'seatPreferences' => $seatPreferences]);
    }

    /**
     * Show the form for creating a new price preference
     */
    /**
     * Create
     */
    public function create(): View
    {
        $eventCategories = UserPricePreference::getEventCategories();
        $seatPreferences = UserPricePreference::getSeatPreferences();
        $alertFrequencies = UserPricePreference::getAlertFrequencies();

        return view('preferences.prices.create', ['eventCategories' => $eventCategories, 'seatPreferences' => $seatPreferences, 'alertFrequencies' => $alertFrequencies]);
    }

    /**
     * Store a newly created price preference
     */
    /**
     * Store
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
          'preference_name'          => 'required|string|max:255',
          'sport_type'               => 'nullable|string|max:100',
          'event_category'           => 'nullable|string|in:' . implode(',', array_keys(UserPricePreference::getEventCategories())),
          'min_price'                => 'nullable|numeric|min:0',
          'max_price'                => 'required|numeric|min:0',
          'preferred_quantity'       => 'integer|min:1|max:20',
          'seat_preferences'         => 'nullable|array',
          'seat_preferences.*'       => 'string|in:' . implode(',', array_keys(UserPricePreference::getSeatPreferences())),
          'section_preferences'      => 'nullable|array',
          'section_preferences.*'    => 'string|max:100',
          'price_drop_threshold'     => 'nullable|numeric|min:0|max:100',
          'price_increase_threshold' => 'nullable|numeric|min:0|max:100',
          'auto_purchase_enabled'    => 'boolean',
          'auto_purchase_max_price'  => 'nullable|numeric|min:0',
          'email_alerts'             => 'boolean',
          'push_alerts'              => 'boolean',
          'sms_alerts'               => 'boolean',
          'alert_frequency'          => 'required|string|in:' . implode(',', array_keys(UserPricePreference::getAlertFrequencies())),
          'is_active'                => 'boolean',
        ]);

        // Validate the preference data
        $validationErrors = UserPricePreference::validatePreferenceData($validated);
        if ($validationErrors !== []) {
            if ($request->wantsJson()) {
                return response()->json([
                  'errors' => $validationErrors,
                ], 422);
            }

            return back()->withErrors($validationErrors)->withInput();
        }

        $validated['user_id'] = $user->id;
        $validated['email_alerts'] = $request->boolean('email_alerts', TRUE);
        $validated['push_alerts'] = $request->boolean('push_alerts', FALSE);
        $validated['sms_alerts'] = $request->boolean('sms_alerts', FALSE);
        $validated['auto_purchase_enabled'] = $request->boolean('auto_purchase_enabled', FALSE);
        $validated['is_active'] = $request->boolean('is_active', TRUE);
        $validated['preferred_quantity'] ??= 2;

        $preference = UserPricePreference::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
              'preference' => $preference,
              'message'    => 'Price preference created successfully!',
            ], 201);
        }

        return redirect()->route('preferences.prices.index')
          ->with('success', 'Price preference created successfully!');
    }

    /**
     * Display the specified price preference
     */
    /**
     * Show
     */
    public function show(UserPricePreference $preference): View|JsonResponse
    {
        $this->authorize('view', $preference);

        if (request()->wantsJson()) {
            return response()->json(['preference' => $preference]);
        }

        return view('preferences.prices.show', ['preference' => $preference]);
    }

    /**
     * Show the form for editing the specified price preference
     */
    /**
     * Edit
     */
    public function edit(UserPricePreference $preference): View
    {
        $this->authorize('update', $preference);

        $eventCategories = UserPricePreference::getEventCategories();
        $seatPreferences = UserPricePreference::getSeatPreferences();
        $alertFrequencies = UserPricePreference::getAlertFrequencies();

        return view('preferences.prices.edit', ['preference' => $preference, 'eventCategories' => $eventCategories, 'seatPreferences' => $seatPreferences, 'alertFrequencies' => $alertFrequencies]);
    }

    /**
     * Update the specified price preference
     */
    /**
     * Update
     */
    public function update(Request $request, UserPricePreference $preference): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $preference);

        $validated = $request->validate([
          'preference_name'          => 'required|string|max:255',
          'sport_type'               => 'nullable|string|max:100',
          'event_category'           => 'nullable|string|in:' . implode(',', array_keys(UserPricePreference::getEventCategories())),
          'min_price'                => 'nullable|numeric|min:0',
          'max_price'                => 'required|numeric|min:0',
          'preferred_quantity'       => 'integer|min:1|max:20',
          'seat_preferences'         => 'nullable|array',
          'seat_preferences.*'       => 'string|in:' . implode(',', array_keys(UserPricePreference::getSeatPreferences())),
          'section_preferences'      => 'nullable|array',
          'section_preferences.*'    => 'string|max:100',
          'price_drop_threshold'     => 'nullable|numeric|min:0|max:100',
          'price_increase_threshold' => 'nullable|numeric|min:0|max:100',
          'auto_purchase_enabled'    => 'boolean',
          'auto_purchase_max_price'  => 'nullable|numeric|min:0',
          'email_alerts'             => 'boolean',
          'push_alerts'              => 'boolean',
          'sms_alerts'               => 'boolean',
          'alert_frequency'          => 'required|string|in:' . implode(',', array_keys(UserPricePreference::getAlertFrequencies())),
          'is_active'                => 'boolean',
        ]);

        // Validate the preference data
        $validationErrors = UserPricePreference::validatePreferenceData($validated);
        if ($validationErrors !== []) {
            if ($request->wantsJson()) {
                return response()->json([
                  'errors' => $validationErrors,
                ], 422);
            }

            return back()->withErrors($validationErrors)->withInput();
        }

        $validated['email_alerts'] = $request->boolean('email_alerts');
        $validated['push_alerts'] = $request->boolean('push_alerts');
        $validated['sms_alerts'] = $request->boolean('sms_alerts');
        $validated['auto_purchase_enabled'] = $request->boolean('auto_purchase_enabled');
        $validated['is_active'] = $request->boolean('is_active');

        $preference->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
              'preference' => $preference->fresh(),
              'message'    => 'Price preference updated successfully!',
            ]);
        }

        return redirect()->route('preferences.prices.index')
          ->with('success', 'Price preference updated successfully!');
    }

    /**
     * Remove the specified price preference
     */
    /**
     * Destroy
     */
    public function destroy(UserPricePreference $preference): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $preference);

        $preferenceName = $preference->preference_name;
        $preference->delete();

        if (request()->wantsJson()) {
            return response()->json([
              'message' => "Deleted price preference: {$preferenceName}",
            ]);
        }

        return redirect()->route('preferences.prices.index')
          ->with('success', "Deleted price preference: {$preferenceName}");
    }

    /**
     * Toggle active status for a price preference
     */
    /**
     * ToggleActive
     */
    public function toggleActive(UserPricePreference $preference): JsonResponse
    {
        $this->authorize('update', $preference);

        $preference->update(['is_active' => !$preference->is_active]);

        return response()->json([
          'is_active' => $preference->is_active,
          'message'   => $preference->is_active ? 'Preference activated' : 'Preference deactivated',
        ]);
    }

    /**
     * Update notification settings for a price preference
     */
    /**
     * UpdateNotifications
     */
    public function updateNotifications(Request $request, UserPricePreference $preference): JsonResponse
    {
        $this->authorize('update', $preference);

        $validated = $request->validate([
          'email_alerts'    => 'boolean',
          'push_alerts'     => 'boolean',
          'sms_alerts'      => 'boolean',
          'alert_frequency' => 'string|in:' . implode(',', array_keys(UserPricePreference::getAlertFrequencies())),
        ]);

        $preference->updateNotificationSettings($validated);

        return response()->json([
          'message'  => 'Notification settings updated',
          'settings' => $preference->getNotificationSettings(),
        ]);
    }

    /**
     * Clone a price preference
     */
    /**
     * Clone
     */
    public function clone(Request $request, UserPricePreference $preference): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $preference);

        $validated = $request->validate([
          'sport_type'     => 'nullable|string|max:100',
          'event_category' => 'nullable|string',
        ]);

        $clonedPreference = $preference->cloneFor(
            $validated['sport_type'] ?? NULL,
            $validated['event_category'] ?? NULL,
        );

        if ($request->wantsJson()) {
            return response()->json([
              'preference' => $clonedPreference,
              'message'    => 'Price preference cloned successfully!',
            ]);
        }

        return redirect()->route('preferences.prices.index')
          ->with('success', 'Price preference cloned successfully!');
    }

    /**
     * Test a price preference against sample data
     */
    /**
     * Test
     */
    public function test(Request $request, UserPricePreference $preference): JsonResponse
    {
        $this->authorize('view', $preference);

        $validated = $request->validate([
          'ticket_price' => 'required|numeric|min:0',
          'old_price'    => 'nullable|numeric|min:0',
          'seat_info'    => 'nullable|array',
          'section'      => 'nullable|string',
        ]);

        $results = [
          'matches_price'         => $preference->matchesPrice($validated['ticket_price']),
          'formatted_price_range' => $preference->getFormattedPriceRange(),
          'average_target_price'  => $preference->getAverageTargetPrice(),
          'should_auto_purchase'  => $preference->shouldAutoPurchase($validated['ticket_price']),
        ];

        if (isset($validated['old_price'])) {
            $results['significant_price_drop'] = $preference->isPriceDropSignificant(
                $validated['old_price'],
                $validated['ticket_price'],
            );
            $results['significant_price_increase'] = $preference->isPriceIncreaseSignificant(
                $validated['old_price'],
                $validated['ticket_price'],
            );
        }

        if (isset($validated['seat_info'])) {
            $results['matches_seat_preferences'] = $preference->matchesSeatPreferences($validated['seat_info']);
        }

        if (isset($validated['section'])) {
            $results['matches_section_preferences'] = $preference->matchesSectionPreferences($validated['section']);
        }

        return response()->json([
          'test_results'   => $results,
          'recommendation' => $this->generateRecommendation($preference, $results),
        ]);
    }

    /**
     * Import price preferences from a list or CSV
     */
    /**
     * Import
     */
    public function import(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $request->validate([
          'preferences'                   => 'required|array',
          'preferences.*.preference_name' => 'required|string',
          'preferences.*.max_price'       => 'required|numeric|min:0',
          'preferences.*.sport_type'      => 'nullable|string',
          'preferences.*.event_category'  => 'nullable|string',
        ]);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($request->preferences as $prefData) {
            try {
                // Check if preference already exists
                $existing = UserPricePreference::where('user_id', $user->id)
                  ->where('preference_name', $prefData['preference_name'])
                  ->exists();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                UserPricePreference::create([
                  'user_id'            => $user->id,
                  'preference_name'    => $prefData['preference_name'],
                  'max_price'          => $prefData['max_price'],
                  'sport_type'         => $prefData['sport_type'] ?? NULL,
                  'event_category'     => $prefData['event_category'] ?? NULL,
                  'preferred_quantity' => 2,
                  'email_alerts'       => TRUE,
                  'push_alerts'        => FALSE,
                  'sms_alerts'         => FALSE,
                  'alert_frequency'    => 'immediate',
                  'is_active'          => TRUE,
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import {$prefData['preference_name']}: " . $e->getMessage();
            }
        }

        $message = "Import completed. {$imported} preferences added";
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

        $redirectResponse = redirect()->route('preferences.prices.index')
          ->with('success', $message);

        if ($errors !== []) {
            $redirectResponse->with('errors', $errors);
        }

        return $redirectResponse;
    }

    /**
     * Export user's price preferences
     */
    /**
     * Export
     */
    public function export(Request $request): Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $preferences = UserPricePreference::where('user_id', $user->id)
          ->orderBy('is_active', 'desc')
          ->orderBy('preference_name')
          ->get();

        $format = $request->get('format', 'json');

        if ($format === 'csv') {
            $headers = [
              'Content-Type'        => 'text/csv',
              'Content-Disposition' => 'attachment; filename="price_preferences.csv"',
            ];

            $callback = function () use ($preferences): void {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                  'Preference Name',
                  'Sport Type',
                  'Event Category',
                  'Min Price',
                  'Max Price',
                  'Preferred Quantity',
                  'Email Alerts',
                  'Push Alerts',
                  'SMS Alerts',
                  'Alert Frequency',
                  'Auto Purchase',
                  'Auto Purchase Max Price',
                  'Is Active',
                ]);

                foreach ($preferences as $pref) {
                    fputcsv($file, [
                      $pref->preference_name,
                      $pref->sport_type,
                      $pref->event_category,
                      $pref->min_price,
                      $pref->max_price,
                      $pref->preferred_quantity,
                      $pref->email_alerts ? 'Yes' : 'No',
                      $pref->push_alerts ? 'Yes' : 'No',
                      $pref->sms_alerts ? 'Yes' : 'No',
                      $pref->alert_frequency,
                      $pref->auto_purchase_enabled ? 'Yes' : 'No',
                      $pref->auto_purchase_max_price,
                      $pref->is_active ? 'Yes' : 'No',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Default JSON export
        return response()->json([
          'preferences' => $preferences,
          'exported_at' => now()->toISOString(),
          'total_count' => $preferences->count(),
        ]);
    }

    /**
     * Bulk update price preferences
     */
    /**
     * BulkUpdate
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
          'preference_ids'   => 'required|array',
          'preference_ids.*' => 'exists:user_price_preferences,id',
          'action'           => 'required|in:activate,deactivate,update_notifications,delete',
          'email_alerts'     => 'boolean',
          'push_alerts'      => 'boolean',
          'sms_alerts'       => 'boolean',
          'alert_frequency'  => 'string|in:' . implode(',', array_keys(UserPricePreference::getAlertFrequencies())),
        ]);

        $preferences = UserPricePreference::whereIn('id', $validated['preference_ids'])
          ->where('user_id', $user->id)
          ->get();

        $updated = 0;

        foreach ($preferences as $pref) {
            switch ($validated['action']) {
                case 'activate':
                    $pref->update(['is_active' => TRUE]);
                    $updated++;

                    break;
                case 'deactivate':
                    $pref->update(['is_active' => FALSE]);
                    $updated++;

                    break;
                case 'update_notifications':
                    $pref->updateNotificationSettings([
                      'email'     => $request->boolean('email_alerts'),
                      'push'      => $request->boolean('push_alerts'),
                      'sms'       => $request->boolean('sms_alerts'),
                      'frequency' => $request->get('alert_frequency'),
                    ]);
                    $updated++;

                    break;
                case 'delete':
                    $pref->delete();
                    $updated++;

                    break;
            }
        }

        return response()->json([
          'message'       => "Updated {$updated} price preferences",
          'updated_count' => $updated,
        ]);
    }

    /**
     * Get similar preferences for suggestions
     */
    /**
     * Get  similar
     */
    public function getSimilar(UserPricePreference $preference): JsonResponse
    {
        $this->authorize('view', $preference);

        $similarPreferences = $preference->getSimilarPreferences(10);

        return response()->json([
          'similar_preferences' => $similarPreferences,
          'base_preference'     => [
            'id'        => $preference->id,
            'name'      => $preference->preference_name,
            'max_price' => $preference->max_price,
          ],
        ]);
    }

    /**
     * Generate recommendation based on test results
     */
    /**
     * GenerateRecommendation
     */
    private function generateRecommendation(UserPricePreference $preference, array $results): string
    {
        $recommendations = [];

        if (!$results['matches_price'] && isset($results['ticket_price'])) {
            $recommendations[] = 'This ticket price is outside your preferred range of ' . $preference->getFormattedPriceRange();
        }

        if ($results['should_auto_purchase']) {
            $recommendations[] = 'This ticket meets your auto-purchase criteria';
        }

        if (isset($results['significant_price_drop']) && $results['significant_price_drop']) {
            $recommendations[] = 'This ticket has experienced a significant price drop';
        }

        if (isset($results['matches_seat_preferences']) && !$results['matches_seat_preferences']) {
            $recommendations[] = 'This ticket does not match your seat preferences';
        }

        if (isset($results['matches_section_preferences']) && !$results['matches_section_preferences']) {
            $recommendations[] = 'This ticket is not in your preferred sections';
        }

        if ($recommendations === []) {
            $recommendations[] = 'This ticket matches your preferences';
        }

        return implode('. ', $recommendations) . '.';
    }
}
