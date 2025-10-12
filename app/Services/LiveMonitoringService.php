<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapedTicket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveMonitoringService
{
    /**
     * Get monitored platforms with their status
     */
    public function getMonitoredPlatforms(): array
    {
        return Cache::remember('monitored_platforms', 600, function () {
            return [
                [
                    'name'          => 'Ticketmaster UK',
                    'slug'          => 'ticketmaster',
                    'logo'          => 'https://logos-world.net/wp-content/uploads/2021/03/Ticketmaster-Logo.png',
                    'status'        => $this->checkPlatformStatus('ticketmaster'),
                    'last_scrape'   => ScrapedTicket::where('platform', 'ticketmaster')->latest()->value('created_at'),
                    'total_tickets' => ScrapedTicket::where('platform', 'ticketmaster')->count(),
                    'verified'      => TRUE,
                    'features'      => ['Official', 'Verified', 'Real-time'],
                ],
                [
                    'name'          => 'StubHub UK',
                    'slug'          => 'stubhub',
                    'logo'          => 'https://logos-world.net/wp-content/uploads/2021/03/StubHub-Logo.png',
                    'status'        => $this->checkPlatformStatus('stubhub'),
                    'last_scrape'   => ScrapedTicket::where('platform', 'stubhub')->latest()->value('created_at'),
                    'total_tickets' => ScrapedTicket::where('platform', 'stubhub')->count(),
                    'verified'      => TRUE,
                    'features'      => ['Secondary Market', 'Instant Download'],
                ],
                [
                    'name'          => 'Viagogo',
                    'slug'          => 'viagogo',
                    'logo'          => 'https://logos-world.net/wp-content/uploads/2021/03/Viagogo-Logo.png',
                    'status'        => $this->checkPlatformStatus('viagogo'),
                    'last_scrape'   => ScrapedTicket::where('platform', 'viagogo')->latest()->value('created_at'),
                    'total_tickets' => ScrapedTicket::where('platform', 'viagogo')->count(),
                    'verified'      => TRUE,
                    'features'      => ['Global Platform', '24/7 Support'],
                ],
                [
                    'name'          => 'See Tickets',
                    'slug'          => 'seetickets',
                    'logo'          => '/images/platforms/seetickets-logo.png',
                    'status'        => $this->checkPlatformStatus('seetickets'),
                    'last_scrape'   => ScrapedTicket::where('platform', 'seetickets')->latest()->value('created_at'),
                    'total_tickets' => ScrapedTicket::where('platform', 'seetickets')->count(),
                    'verified'      => TRUE,
                    'features'      => ['UK Focused', 'Official Distributor'],
                ],
                [
                    'name'          => 'Eventim UK',
                    'slug'          => 'eventim',
                    'logo'          => '/images/platforms/eventim-logo.png',
                    'status'        => $this->checkPlatformStatus('eventim'),
                    'last_scrape'   => ScrapedTicket::where('platform', 'eventim')->latest()->value('created_at'),
                    'total_tickets' => ScrapedTicket::where('platform', 'eventim')->count(),
                    'verified'      => TRUE,
                    'features'      => ['European Coverage', 'Mobile Tickets'],
                ],
                [
                    'name'          => 'Live Nation UK',
                    'slug'          => 'livenation',
                    'logo'          => '/images/platforms/livenation-logo.png',
                    'status'        => $this->checkPlatformStatus('livenation'),
                    'last_scrape'   => ScrapedTicket::where('platform', 'livenation')->latest()->value('created_at'),
                    'total_tickets' => ScrapedTicket::where('platform', 'livenation')->count(),
                    'verified'      => TRUE,
                    'features'      => ['Major Venues', 'VIP Packages'],
                ],
            ];
        });
    }

    /**
     * Get supported leagues/competitions
     */
    public function getSupportedLeagues(): array
    {
        return Cache::remember('supported_leagues', 3600, function () {
            $leagues = ScrapedTicket::select('sport_type')
                ->selectRaw('COUNT(*) as ticket_count')
                ->whereNotNull('sport_type')
                ->groupBy('sport_type')
                ->orderByDesc('ticket_count')
                ->get()
                ->map(function ($league) {
                    return [
                        'name'         => $this->formatLeagueName($league->sport_type),
                        'slug'         => $league->sport_type,
                        'ticket_count' => $league->ticket_count,
                        'logo'         => $this->getLeagueLogo($league->sport_type),
                        'category'     => $this->getLeagueCategory($league->sport_type),
                    ];
                })
                ->toArray();

            // Add predefined leagues with logos
            $predefinedLeagues = [
                [
                    'name'         => 'Premier League',
                    'slug'         => 'premier_league',
                    'ticket_count' => ScrapedTicket::where('sport_type', 'premier_league')->count(),
                    'logo'         => '/images/leagues/premier-league.png',
                    'category'     => 'Football',
                ],
                [
                    'name'         => 'Champions League',
                    'slug'         => 'champions_league',
                    'ticket_count' => ScrapedTicket::where('sport_type', 'champions_league')->count(),
                    'logo'         => '/images/leagues/champions-league.png',
                    'category'     => 'Football',
                ],
                [
                    'name'         => 'Europa League',
                    'slug'         => 'europa_league',
                    'ticket_count' => ScrapedTicket::where('sport_type', 'europa_league')->count(),
                    'logo'         => '/images/leagues/europa-league.png',
                    'category'     => 'Football',
                ],
                [
                    'name'         => 'FA Cup',
                    'slug'         => 'fa_cup',
                    'ticket_count' => ScrapedTicket::where('sport_type', 'fa_cup')->count(),
                    'logo'         => '/images/leagues/fa-cup.png',
                    'category'     => 'Football',
                ],
            ];

            // Merge and deduplicate
            $allLeagues = collect($predefinedLeagues)->merge($leagues)
                ->unique('slug')
                ->sortByDesc('ticket_count')
                ->values()
                ->toArray();

            return $allLeagues;
        });
    }

    /**
     * Check platform status (online/offline)
     */
    private function checkPlatformStatus(string $platform): array
    {
        $cacheKey = "platform_status_{$platform}";

        return Cache::remember($cacheKey, 300, function () use ($platform) {
            try {
                $urls = [
                    'ticketmaster' => 'https://www.ticketmaster.co.uk',
                    'stubhub'      => 'https://www.stubhub.co.uk',
                    'viagogo'      => 'https://www.viagogo.com',
                    'seetickets'   => 'https://www.seetickets.com',
                    'eventim'      => 'https://www.eventim.co.uk',
                    'livenation'   => 'https://www.livenation.co.uk',
                ];

                if (!isset($urls[$platform])) {
                    return ['status' => 'unknown', 'response_time' => NULL];
                }

                $start = microtime(TRUE);
                $response = Http::timeout(10)->get($urls[$platform]);
                $responseTime = round((microtime(TRUE) - $start) * 1000);

                if ($response->successful()) {
                    return [
                        'status'        => 'online',
                        'response_time' => $responseTime,
                        'checked_at'    => now()->toISOString(),
                    ];
                }

                return [
                    'status'        => 'error',
                    'response_time' => $responseTime,
                    'error_code'    => $response->status(),
                    'checked_at'    => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                Log::warning("Platform status check failed for {$platform}: " . $e->getMessage());

                return [
                    'status'        => 'offline',
                    'response_time' => NULL,
                    'error'         => $e->getMessage(),
                    'checked_at'    => now()->toISOString(),
                ];
            }
        });
    }

    /**
     * Get platform status summary for all platforms
     */
    public function getPlatformStatus(): array
    {
        $platforms = $this->getMonitoredPlatforms();

        return [
            'summary' => [
                'total_platforms' => count($platforms),
                'online'          => collect($platforms)->where('status.status', 'online')->count(),
                'offline'         => collect($platforms)->where('status.status', 'offline')->count(),
                'error'           => collect($platforms)->where('status.status', 'error')->count(),
            ],
            'platforms'    => $platforms,
            'last_updated' => now()->toISOString(),
        ];
    }

    /**
     * Format league name for display
     */
    private function formatLeagueName(string $leagueSlug): string
    {
        return match ($leagueSlug) {
            'premier_league'   => 'Premier League',
            'champions_league' => 'Champions League',
            'europa_league'    => 'Europa League',
            'fa_cup'           => 'FA Cup',
            'carabao_cup'      => 'Carabao Cup',
            'championship'     => 'Championship',
            'league_one'       => 'League One',
            'league_two'       => 'League Two',
            'world_cup'        => 'World Cup',
            'euros'            => 'European Championship',
            default            => ucwords(str_replace('_', ' ', $leagueSlug)),
        };
    }

    /**
     * Get league logo path
     */
    private function getLeagueLogo(string $leagueSlug): string
    {
        $logos = [
            'premier_league'   => '/images/leagues/premier-league.png',
            'champions_league' => '/images/leagues/champions-league.png',
            'europa_league'    => '/images/leagues/europa-league.png',
            'fa_cup'           => '/images/leagues/fa-cup.png',
            'carabao_cup'      => '/images/leagues/carabao-cup.png',
            'championship'     => '/images/leagues/championship.png',
        ];

        return $logos[$leagueSlug] ?? '/images/leagues/default.png';
    }

    /**
     * Get league category
     */
    private function getLeagueCategory(string $leagueSlug): string
    {
        $footballLeagues = [
            'premier_league', 'champions_league', 'europa_league', 'fa_cup',
            'carabao_cup', 'championship', 'league_one', 'league_two',
        ];

        if (in_array($leagueSlug, $footballLeagues)) {
            return 'Football';
        }

        return 'Other';
    }

    /**
     * Get system monitoring statistics
     */
    public function getSystemStats(): array
    {
        return Cache::remember('system_monitoring_stats', 60, function () {
            return [
                'monitoring' => [
                    'active_alerts'            => \App\Models\TicketAlert::active()->count(),
                    'total_users'              => \App\Models\User::count(),
                    'notifications_sent_today' => $this->getNotificationsSentToday(),
                    'avg_response_time'        => $this->getAverageResponseTime(),
                ],
                'tickets' => [
                    'total_monitored'   => ScrapedTicket::count(),
                    'available_now'     => ScrapedTicket::where('is_available', TRUE)->count(),
                    'high_demand'       => ScrapedTicket::where('is_high_demand', TRUE)->count(),
                    'price_drops_today' => ScrapedTicket::where('price_changed_at', '>=', today())->count(),
                ],
                'platforms' => [
                    'total_platforms'  => count($this->getMonitoredPlatforms()),
                    'active_platforms' => $this->getActivePlatformsCount(),
                    'last_scrape'      => ScrapedTicket::latest()->value('created_at'),
                ],
            ];
        });
    }

    private function getNotificationsSentToday(): int
    {
        // This would come from a notifications log table
        return 0; // Placeholder
    }

    private function getAverageResponseTime(): float
    {
        $platforms = $this->getMonitoredPlatforms();
        $responseTimes = collect($platforms)
            ->pluck('status.response_time')
            ->filter()
            ->toArray();

        return count($responseTimes) > 0 ? round(array_sum($responseTimes) / count($responseTimes), 2) : 0;
    }

    private function getActivePlatformsCount(): int
    {
        return collect($this->getMonitoredPlatforms())
            ->where('status.status', 'online')
            ->count();
    }
}
