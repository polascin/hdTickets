<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Following;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use function array_slice;

/**
 * Team & Venue Following System API Controller
 *
 * Manages user following relationships with teams and venues
 * Provides discovery, activity feeds, and notification controls
 */
class FollowingController extends Controller
{
    /**
     * Get user's following dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'following');

        $data = Cache::remember("following_dashboard_{$user->id}_{$tab}", 300, function () use ($user, $tab) {
            if ($tab === 'following') {
                return $this->getFollowingData($user);
            }

            return $this->getDiscoverData($user);
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get user's followed teams and venues
     */
    public function getFollowing(Request $request): JsonResponse
    {
        $user = Auth::user();
        $type = $request->get('type', 'all'); // all, teams, venues
        $sortBy = $request->get('sort_by', 'name'); // name, recent_activity, follow_date

        $query = Following::where('user_id', $user->id)
            ->with(['followable']);

        if ($type !== 'all') {
            $modelClass = $type === 'teams' ? Team::class : Venue::class;
            $query->where('followable_type', $modelClass);
        }

        switch ($sortBy) {
            case 'recent_activity':
                $query->orderBy('last_activity_at', 'desc');

                break;
            case 'follow_date':
                $query->orderBy('created_at', 'desc');

                break;
            case 'name':
            default:
                $query->join('teams', function ($join): void {
                    $join->on('followings.followable_id', '=', 'teams.id')
                        ->where('followings.followable_type', Team::class);
                })
                    ->join('venues', function ($join): void {
                        $join->on('followings.followable_id', '=', 'venues.id')
                            ->where('followings.followable_type', Venue::class);
                    })
                    ->orderByRaw('COALESCE(teams.name, venues.name)');

                break;
        }

        $following = $query->get();

        $data = $following->map(function ($follow) {
            $item = $follow->followable;

            if ($item instanceof Team) {
                return [
                    'id'                    => $item->uuid,
                    'type'                  => 'team',
                    'name'                  => $item->name,
                    'sport'                 => $item->sport,
                    'league'                => $item->league,
                    'logo'                  => $item->logo_url,
                    'followers_count'       => $item->followers_count,
                    'upcoming_events'       => $this->getUpcomingEvents($item, 'team'),
                    'recent_activity'       => $this->getRecentActivity($item, 'team'),
                    'notifications_enabled' => $follow->notifications_enabled,
                    'followed_at'           => $follow->created_at,
                ];
            }
            if ($item instanceof Venue) {
                return [
                    'id'                    => $item->uuid,
                    'type'                  => 'venue',
                    'name'                  => $item->name,
                    'city'                  => $item->city,
                    'capacity'              => $item->capacity,
                    'image'                 => $item->image_url,
                    'followers_count'       => $item->followers_count,
                    'upcoming_events'       => $this->getUpcomingEvents($item, 'venue'),
                    'recent_activity'       => $this->getRecentActivity($item, 'venue'),
                    'notifications_enabled' => $follow->notifications_enabled,
                    'followed_at'           => $follow->created_at,
                ];
            }
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Get discover recommendations
     */
    public function discover(Request $request): JsonResponse
    {
        $user = Auth::user();
        $type = $request->get('type', 'all');
        $category = $request->get('category', 'trending');

        $data = Cache::remember("discover_{$user->id}_{$type}_{$category}", 1800, function () use ($user, $type, $category) {
            $recommendations = [];

            // Get user's current following to exclude
            $followingIds = Following::where('user_id', $user->id)
                ->pluck('followable_id', 'followable_type');

            if ($type === 'all' || $type === 'teams') {
                $teams = $this->getTeamRecommendations($user, $category, $followingIds[Team::class] ?? []);
                $recommendations = array_merge($recommendations, $teams);
            }

            if ($type === 'all' || $type === 'venues') {
                $venues = $this->getVenueRecommendations($user, $category, $followingIds[Venue::class] ?? []);
                $recommendations = array_merge($recommendations, $venues);
            }

            // Sort by recommendation score
            usort($recommendations, function ($a, $b) {
                return $b['recommendation_score'] <=> $a['recommendation_score'];
            });

            return array_slice($recommendations, 0, 20);
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Follow a team or venue
     */
    public function follow(Request $request): JsonResponse
    {
        $request->validate([
            'type'          => 'required|string|in:team,venue',
            'id'            => 'required|string',
            'notifications' => 'boolean',
        ]);

        $user = Auth::user();
        $type = $request->get('type');
        $id = $request->get('id');
        $notifications = $request->get('notifications', TRUE);

        $modelClass = $type === 'team' ? Team::class : Venue::class;
        $item = $modelClass::where('uuid', $id)->firstOrFail();

        // Check if already following
        $existing = Following::where('user_id', $user->id)
            ->where('followable_type', $modelClass)
            ->where('followable_id', $item->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Already following this ' . $type,
            ], 400);
        }

        // Create following relationship
        Following::create([
            'user_id'               => $user->id,
            'followable_type'       => $modelClass,
            'followable_id'         => $item->id,
            'notifications_enabled' => $notifications,
        ]);

        // Update followers count
        $item->increment('followers_count');

        // Clear cache
        Cache::forget("following_dashboard_{$user->id}_following");
        Cache::forget("following_dashboard_{$user->id}_discover");

        return response()->json([
            'success'   => TRUE,
            'message'   => ucfirst($type) . ' followed successfully',
            'following' => TRUE,
        ]);
    }

    /**
     * Unfollow a team or venue
     */
    public function unfollow(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:team,venue',
            'id'   => 'required|string',
        ]);

        $user = Auth::user();
        $type = $request->get('type');
        $id = $request->get('id');

        $modelClass = $type === 'team' ? Team::class : Venue::class;
        $item = $modelClass::where('uuid', $id)->firstOrFail();

        $following = Following::where('user_id', $user->id)
            ->where('followable_type', $modelClass)
            ->where('followable_id', $item->id)
            ->first();

        if (!$following) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Not following this ' . $type,
            ], 400);
        }

        $following->delete();

        // Update followers count
        $item->decrement('followers_count');

        // Clear cache
        Cache::forget("following_dashboard_{$user->id}_following");
        Cache::forget("following_dashboard_{$user->id}_discover");

        return response()->json([
            'success'   => TRUE,
            'message'   => 'Unfollowed successfully',
            'following' => FALSE,
        ]);
    }

    /**
     * Toggle notifications for followed item
     */
    public function toggleNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'type'    => 'required|string|in:team,venue',
            'id'      => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $user = Auth::user();
        $type = $request->get('type');
        $id = $request->get('id');
        $enabled = $request->get('enabled');

        $modelClass = $type === 'team' ? Team::class : Venue::class;
        $item = $modelClass::where('uuid', $id)->firstOrFail();

        $following = Following::where('user_id', $user->id)
            ->where('followable_type', $modelClass)
            ->where('followable_id', $item->id)
            ->firstOrFail();

        $following->update(['notifications_enabled' => $enabled]);

        return response()->json([
            'success'               => TRUE,
            'message'               => 'Notification settings updated',
            'notifications_enabled' => $enabled,
        ]);
    }

    /**
     * Get following statistics
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = Cache::remember("following_stats_{$user->id}", 600, function () use ($user) {
            $following = Following::where('user_id', $user->id);

            return [
                'total_following'       => $following->count(),
                'teams_following'       => $following->where('followable_type', Team::class)->count(),
                'venues_following'      => $following->where('followable_type', Venue::class)->count(),
                'notifications_enabled' => $following->where('notifications_enabled', TRUE)->count(),
                'recent_activity_count' => $this->getRecentActivityCount($user),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $stats,
        ]);
    }

    /**
     * Get following dashboard data
     */
    private function getFollowingData(User $user): array
    {
        $following = Following::where('user_id', $user->id)
            ->with(['followable'])
            ->orderBy('last_activity_at', 'desc')
            ->limit(20)
            ->get();

        $data = $following->map(function ($follow) {
            return $this->formatFollowingItem($follow);
        });

        return [
            'following' => $data,
            'stats'     => [
                'total_following'    => $following->count(),
                'with_notifications' => $following->where('notifications_enabled', TRUE)->count(),
                'recent_activity'    => $this->getRecentActivityCount($user),
            ],
        ];
    }

    /**
     * Get discover dashboard data
     */
    private function getDiscoverData(User $user): array
    {
        $followingIds = Following::where('user_id', $user->id)
            ->pluck('followable_id', 'followable_type');

        $trending = [
            'teams' => Team::whereNotIn('id', $followingIds[Team::class] ?? [])
                ->orderBy('followers_count', 'desc')
                ->limit(6)
                ->get(),
            'venues' => Venue::whereNotIn('id', $followingIds[Venue::class] ?? [])
                ->orderBy('followers_count', 'desc')
                ->limit(6)
                ->get(),
        ];

        return [
            'trending'        => $trending,
            'categories'      => $this->getDiscoverCategories(),
            'recommendations' => $this->getPersonalisedRecommendations($user),
        ];
    }

    /**
     * Get team recommendations
     */
    private function getTeamRecommendations(User $user, string $category, array $excludeIds): array
    {
        $query = Team::whereNotIn('id', $excludeIds);

        switch ($category) {
            case 'trending':
                $query->orderBy('followers_count', 'desc');

                break;
            case 'popular':
                $query->orderBy('popularity_score', 'desc');

                break;
            case 'local':
                // You would need location logic here
                break;
        }

        return $query->limit(10)->get()->map(function ($team) {
            return [
                'id'                    => $team->uuid,
                'type'                  => 'team',
                'name'                  => $team->name,
                'sport'                 => $team->sport,
                'league'                => $team->league,
                'logo'                  => $team->logo_url,
                'followers_count'       => $team->followers_count,
                'recommendation_score'  => $this->calculateTeamScore($team),
                'upcoming_events_count' => $this->getUpcomingEventsCount($team, 'team'),
            ];
        })->toArray();
    }

    /**
     * Get venue recommendations
     */
    private function getVenueRecommendations(User $user, string $category, array $excludeIds): array
    {
        $query = Venue::whereNotIn('id', $excludeIds);

        switch ($category) {
            case 'trending':
                $query->orderBy('followers_count', 'desc');

                break;
            case 'popular':
                $query->orderBy('popularity_score', 'desc');

                break;
            case 'local':
                // You would need location logic here
                break;
        }

        return $query->limit(10)->get()->map(function ($venue) {
            return [
                'id'                    => $venue->uuid,
                'type'                  => 'venue',
                'name'                  => $venue->name,
                'city'                  => $venue->city,
                'capacity'              => $venue->capacity,
                'image'                 => $venue->image_url,
                'followers_count'       => $venue->followers_count,
                'recommendation_score'  => $this->calculateVenueScore($venue),
                'upcoming_events_count' => $this->getUpcomingEventsCount($venue, 'venue'),
            ];
        })->toArray();
    }

    /**
     * Get upcoming events for a team or venue
     *
     * @param mixed $item
     */
    private function getUpcomingEvents($item, string $type): array
    {
        $query = Event::where('date', '>=', now());

        if ($type === 'team') {
            $query->where(function ($q) use ($item): void {
                $q->where('home_team', $item->name)
                    ->orWhere('away_team', $item->name);
            });
        } else {
            $query->where('venue_id', $item->id);
        }

        return $query->orderBy('date')
            ->limit(3)
            ->get(['name', 'date', 'venue'])
            ->toArray();
    }

    /**
     * Get recent activity for a team or venue
     *
     * @param mixed $item
     */
    private function getRecentActivity($item, string $type): array
    {
        // This would track recent events, ticket releases, etc.
        return [
            [
                'type'      => 'event_scheduled',
                'message'   => 'New event scheduled',
                'timestamp' => now()->subHours(2),
            ],
        ];
    }

    /**
     * Calculate team recommendation score
     */
    private function calculateTeamScore(Team $team): int
    {
        $score = 0;
        $score += min($team->followers_count / 100, 50); // Max 50 points
        $score += min($team->popularity_score ?? 0, 30); // Max 30 points
        $score += $this->getUpcomingEventsCount($team, 'team') * 5; // 5 points per event

        return (int) $score;
    }

    /**
     * Calculate venue recommendation score
     */
    private function calculateVenueScore(Venue $venue): int
    {
        $score = 0;
        $score += min($venue->followers_count / 100, 50);
        $score += min($venue->popularity_score ?? 0, 30);
        $score += $this->getUpcomingEventsCount($venue, 'venue') * 5;

        return (int) $score;
    }

    /**
     * Get upcoming events count
     *
     * @param mixed $item
     */
    private function getUpcomingEventsCount($item, string $type): int
    {
        $query = Event::where('date', '>=', now());

        if ($type === 'team') {
            $query->where(function ($q) use ($item): void {
                $q->where('home_team', $item->name)
                    ->orWhere('away_team', $item->name);
            });
        } else {
            $query->where('venue_id', $item->id);
        }

        return $query->count();
    }

    /**
     * Get recent activity count
     */
    private function getRecentActivityCount(User $user): int
    {
        // Count recent activities from followed teams/venues
        return 5; // Placeholder
    }

    /**
     * Format following item
     */
    private function formatFollowingItem(Following $follow): array
    {
        $item = $follow->followable;

        $data = [
            'id'                    => $item->uuid,
            'type'                  => $item instanceof Team ? 'team' : 'venue',
            'name'                  => $item->name,
            'followers_count'       => $item->followers_count,
            'notifications_enabled' => $follow->notifications_enabled,
            'followed_at'           => $follow->created_at,
        ];

        if ($item instanceof Team) {
            $data['sport'] = $item->sport;
            $data['league'] = $item->league;
            $data['logo'] = $item->logo_url;
        } else {
            $data['city'] = $item->city;
            $data['capacity'] = $item->capacity;
            $data['image'] = $item->image_url;
        }

        return $data;
    }

    /**
     * Get discover categories
     */
    private function getDiscoverCategories(): array
    {
        return [
            ['id' => 'trending', 'label' => 'Trending', 'description' => 'Most followed this week'],
            ['id' => 'popular', 'label' => 'Popular', 'description' => 'All-time favourites'],
            ['id' => 'local', 'label' => 'Local', 'description' => 'Near you'],
            ['id' => 'recommended', 'label' => 'For You', 'description' => 'Based on your interests'],
        ];
    }

    /**
     * Get personalised recommendations
     */
    private function getPersonalisedRecommendations(User $user): array
    {
        // This would use ML or user behaviour analysis
        return [];
    }
}
