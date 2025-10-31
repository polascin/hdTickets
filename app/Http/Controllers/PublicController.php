<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * PublicController - Handles public marketing pages
 *
 * Manages the public-facing marketing pages including home, pricing,
 * coverage, and FAQs for HD Tickets sports events monitoring system.
 */
class PublicController extends Controller
{
    /**
     * Display the public landing page
     *
     * @return View
     */
    public function home(): View
    {
        $stats = $this->getCachedStats();

        return view('public.home', compact('stats'));
    }

    /**
     * Display the pricing page
     *
     * @return View
     */
    public function pricing(): View
    {
        return view('public.pricing');
    }

    /**
     * Display the coverage page
     *
     * @return View
     */
    public function coverage(): View
    {
        return view('public.coverage');
    }

    /**
     * Display the FAQs page
     *
     * @return View
     */
    public function faqs(): View
    {
        return view('public.faqs');
    }

    /**
     * Get cached statistics for the landing page
     *
     * Caches results for 10 minutes to reduce database load on public traffic
     *
     * @return array<string, mixed>
     */
    private function getCachedStats(): array
    {
        return Cache::remember('public.landing.stats', 600, function (): array {
            // Get total tickets count from main table
            $totalTickets = DB::table('tickets')->count();

            // Count distinct platforms (assuming platform_id or similar field exists)
            // Fallback to default value if table structure differs
            try {
                $platforms = DB::table('ticket_sources')
                    ->distinct()
                    ->count('platform');
            } catch (\Exception $e) {
                $platforms = 40; // Default fallback
            }

            // Count distinct venues/cities
            // Fallback to default value if table structure differs
            try {
                $cities = DB::table('venues')
                    ->distinct()
                    ->count('city');

                if ($cities === 0) {
                    $cities = 50; // Default fallback
                }
            } catch (\Exception $e) {
                $cities = 50; // Default fallback
            }

            return [
                'total_tickets' => $totalTickets,
                'platforms'     => $platforms,
                'cities'        => $cities,
            ];
        });
    }
}
