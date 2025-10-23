<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the main customer dashboard
     */
    public function index(): View
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $stats = $this->getDashboardStats($user);
        $recentTickets = $this->getRecentTickets();

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Get dashboard statistics with caching
     */
    private function getDashboardStats(User $user): array
    {
        $cacheKey = 'dashboard_stats_' . $user->id . '_' . now()->format('YmdH');

        return Cache::remember($cacheKey, 900, function () use ($user): array {
            return [
                'active_monitors' => TicketAlert::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->count(),
                'alerts_today' => TicketAlert::where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->count(),
                'price_drops' => 0,
                'available_now' => ScrapedTicket::where('is_available', true)->count(),
            ];
        });
    }

    /**
     * Get recent tickets
     */
    private function getRecentTickets()
    {
        return ScrapedTicket::with(['category'])
            ->where('is_available', true)
            ->latest('scraped_at')
            ->take(10)
            ->get();
    }

    /**
     * AJAX endpoint for dashboard statistics
     */
    public function getStats(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $stats = $this->getDashboardStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
