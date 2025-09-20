<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserFavoriteTeam;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use function strlen;

class UserFavoriteTeamController extends Controller
{
    /**
     * Display the user's favorite teams
     */
    /**
     * Index
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $query = UserFavoriteTeam::where('user_id', $user->id);

        // Apply filters
        if ($request->sport_type) {
            $query->bySport($request->sport_type);
        }

        if ($request->league) {
            $query->byLeague($request->league);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        $teams = $query->orderBy('priority', 'desc')
            ->orderBy('team_name')
            ->paginate(20);

        $stats = UserFavoriteTeam::getTeamStats($user->id);
        $availableSports = UserFavoriteTeam::getAvailableSports();

        if ($request->wantsJson()) {
            return response()->json([
                'teams'  => $teams,
                'stats'  => $stats,
                'sports' => $availableSports,
            ]);
        }

        return view('preferences.teams.index', ['teams' => $teams, 'stats' => $stats, 'availableSports' => $availableSports]);
    }

    /**
     * Show the form for creating a new favorite team
     */
    /**
     * Create
     */
    public function create(): View
    {
        $availableSports = UserFavoriteTeam::getAvailableSports();
        $popularTeams = UserFavoriteTeam::getPopularTeams();

        return view('preferences.teams.create', ['availableSports' => $availableSports, 'popularTeams' => $popularTeams]);
    }

    /**
     * Store a newly created favorite team
     */
    /**
     * Store
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'sport_type'    => 'required|string|in:' . implode(',', array_keys(UserFavoriteTeam::getAvailableSports())),
            'team_name'     => 'required|string|max:255',
            'team_city'     => 'nullable|string|max:255',
            'league'        => 'required|string|max:100',
            'team_logo_url' => 'nullable|url',
            'aliases'       => 'nullable|array',
            'aliases.*'     => 'string|max:255',
            'email_alerts'  => 'boolean',
            'push_alerts'   => 'boolean',
            'sms_alerts'    => 'boolean',
            'priority'      => 'integer|min:1|max:5',
        ]);

        // Check for duplicate team
        $existing = UserFavoriteTeam::where('user_id', $user->id)
            ->where('sport_type', $validated['sport_type'])
            ->where('team_name', $validated['team_name'])
            ->where('league', $validated['league'])
            ->first();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'This team is already in your favorites',
                ], 422);
            }

            return back()->withErrors(['team_name' => 'This team is already in your favorites']);
        }

        $validated['user_id'] = $user->id;
        $validated['email_alerts'] = $request->boolean('email_alerts', TRUE);
        $validated['push_alerts'] = $request->boolean('push_alerts', FALSE);
        $validated['sms_alerts'] = $request->boolean('sms_alerts', FALSE);
        $validated['priority'] ??= 3;

        $team = UserFavoriteTeam::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'team'    => $team,
                'message' => 'Team added to favorites successfully!',
            ], 201);
        }

        return redirect()->route('preferences.teams.index')
            ->with('success', 'Team added to favorites successfully!');
    }

    /**
     * Display the specified favorite team
     */
    /**
     * Show
     */
    public function show(UserFavoriteTeam $team): View|JsonResponse
    {
        $this->authorize('view', $team);

        if (request()->wantsJson()) {
            return response()->json(['team' => $team]);
        }

        return view('preferences.teams.show', ['team' => $team]);
    }

    /**
     * Show the form for editing the specified favorite team
     */
    /**
     * Edit
     */
    public function edit(UserFavoriteTeam $team): View
    {
        $this->authorize('update', $team);

        $availableSports = UserFavoriteTeam::getAvailableSports();
        $leagues = UserFavoriteTeam::getLeaguesBySport($team->sport_type);

        return view('preferences.teams.edit', ['team' => $team, 'availableSports' => $availableSports, 'leagues' => $leagues]);
    }

    /**
     * Update the specified favorite team
     */
    /**
     * Update
     */
    public function update(Request $request, UserFavoriteTeam $team): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'sport_type'    => 'required|string|in:' . implode(',', array_keys(UserFavoriteTeam::getAvailableSports())),
            'team_name'     => 'required|string|max:255',
            'team_city'     => 'nullable|string|max:255',
            'league'        => 'required|string|max:100',
            'team_logo_url' => 'nullable|url',
            'aliases'       => 'nullable|array',
            'aliases.*'     => 'string|max:255',
            'email_alerts'  => 'boolean',
            'push_alerts'   => 'boolean',
            'sms_alerts'    => 'boolean',
            'priority'      => 'integer|min:1|max:5',
        ]);

        $validated['email_alerts'] = $request->boolean('email_alerts');
        $validated['push_alerts'] = $request->boolean('push_alerts');
        $validated['sms_alerts'] = $request->boolean('sms_alerts');

        $team->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'team'    => $team->fresh(),
                'message' => 'Team preferences updated successfully!',
            ]);
        }

        return redirect()->route('preferences.teams.index')
            ->with('success', 'Team preferences updated successfully!');
    }

    /**
     * Remove the specified favorite team
     */
    /**
     * Destroy
     */
    public function destroy(UserFavoriteTeam $team): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $team);

        $teamName = $team->full_name;
        $team->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => "Removed {$teamName} from favorites",
            ]);
        }

        return redirect()->route('preferences.teams.index')
            ->with('success', "Removed {$teamName} from favorites");
    }

    /**
     * Update notification settings for a team
     */
    /**
     * UpdateNotifications
     */
    public function updateNotifications(Request $request, UserFavoriteTeam $team): JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
        ]);

        $team->updateNotificationSettings($validated);

        return response()->json([
            'message'  => 'Notification settings updated',
            'settings' => $team->getNotificationSettings(),
        ]);
    }

    /**
     * Search for teams (autocomplete API)
     */
    /**
     * Search
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q', '');
        $sport = $request->get('sport');

        if (strlen((string) $term) < 2) {
            return response()->json([]);
        }

        $popularTeams = UserFavoriteTeam::getPopularTeams($sport);

        $results = collect($popularTeams)
            ->filter(fn ($team): bool => str_contains(strtolower((string) $team['full_name']), strtolower((string) $term))
                   || str_contains(strtolower((string) $team['name']), strtolower((string) $term))
                   || str_contains(strtolower($team['city'] ?? ''), strtolower((string) $term)))
            ->take(10)
            ->values();

        return response()->json($results);
    }

    /**
     * Get leagues for a specific sport
     */
    /**
     * Get  leagues
     */
    public function getLeagues(Request $request): JsonResponse
    {
        $sport = $request->get('sport');

        if (!$sport) {
            return response()->json(['error' => 'Sport parameter required'], 400);
        }

        $leagues = UserFavoriteTeam::getLeaguesBySport($sport);

        return response()->json($leagues);
    }

    /**
     * Import teams from a list or CSV
     */
    /**
     * Import
     */
    public function import(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'teams'                => 'required|array',
            'teams.*.sport_type'   => 'required|string',
            'teams.*.team_name'    => 'required|string',
            'teams.*.league'       => 'required|string',
            'teams.*.team_city'    => 'nullable|string',
            'default_priority'     => 'integer|min:1|max:5',
            'default_email_alerts' => 'boolean',
        ]);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($request->teams as $teamData) {
            try {
                // Check if team already exists
                $existing = UserFavoriteTeam::where('user_id', $user->id)
                    ->where('sport_type', $teamData['sport_type'])
                    ->where('team_name', $teamData['team_name'])
                    ->where('league', $teamData['league'])
                    ->exists();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                UserFavoriteTeam::create([
                    'user_id'      => $user->id,
                    'sport_type'   => $teamData['sport_type'],
                    'team_name'    => $teamData['team_name'],
                    'team_city'    => $teamData['team_city'] ?? NULL,
                    'league'       => $teamData['league'],
                    'priority'     => $request->get('default_priority', 3),
                    'email_alerts' => $request->boolean('default_email_alerts', TRUE),
                    'push_alerts'  => FALSE,
                    'sms_alerts'   => FALSE,
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import {$teamData['team_name']}: " . $e->getMessage();
            }
        }

        $message = "Import completed. {$imported} teams added";
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

        $redirectResponse = redirect()->route('preferences.teams.index')
            ->with('success', $message);

        if ($errors !== []) {
            $redirectResponse->with('errors', $errors);
        }

        return $redirectResponse;
    }

    /**
     * Export user's favorite teams
     */
    /**
     * Export
     */
    public function export(Request $request): Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $teams = UserFavoriteTeam::where('user_id', $user->id)
            ->orderBy('sport_type')
            ->orderBy('team_name')
            ->get();

        $format = $request->get('format', 'json');

        if ($format === 'csv') {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="favorite_teams.csv"',
            ];

            $callback = function () use ($teams): void {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Sport', 'Team Name', 'City', 'League', 'Priority', 'Email Alerts', 'Push Alerts', 'SMS Alerts']);

                foreach ($teams as $team) {
                    fputcsv($file, [
                        $team->sport_type,
                        $team->team_name,
                        $team->team_city,
                        $team->league,
                        $team->priority,
                        $team->email_alerts ? 'Yes' : 'No',
                        $team->push_alerts ? 'Yes' : 'No',
                        $team->sms_alerts ? 'Yes' : 'No',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Default JSON export
        return response()->json([
            'teams'       => $teams,
            'exported_at' => now()->toISOString(),
            'total_count' => $teams->count(),
        ]);
    }

    /**
     * Bulk update teams (priority, notifications, etc.)
     */
    /**
     * BulkUpdate
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'team_ids'     => 'required|array',
            'team_ids.*'   => 'exists:user_favorite_teams,id',
            'action'       => 'required|in:update_priority,update_notifications,delete',
            'priority'     => 'required_if:action,update_priority|integer|min:1|max:5',
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
        ]);

        $teams = UserFavoriteTeam::whereIn('id', $validated['team_ids'])
            ->where('user_id', $user->id)
            ->get();

        $updated = 0;

        foreach ($teams as $team) {
            switch ($validated['action']) {
                case 'update_priority':
                    $team->update(['priority' => $validated['priority']]);
                    $updated++;

                    break;
                case 'update_notifications':
                    $team->updateNotificationSettings([
                        'email' => $request->boolean('email_alerts'),
                        'push'  => $request->boolean('push_alerts'),
                        'sms'   => $request->boolean('sms_alerts'),
                    ]);
                    $updated++;

                    break;
                case 'delete':
                    $team->delete();
                    $updated++;

                    break;
            }
        }

        return response()->json([
            'message'       => "Updated {$updated} teams",
            'updated_count' => $updated,
        ]);
    }
}
