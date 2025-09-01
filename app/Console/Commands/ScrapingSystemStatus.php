<?php declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScrapedTicket;
use Exception;

class ScrapingSystemStatus extends Command
{
    protected $signature = 'scraping:status {--detailed : Show detailed information}';
    protected $description = 'Display comprehensive status of the scraping system';

    public function handle()
    {
        $detailed = $this->option('detailed');
        
        $this->info('🔍 Scraping System Status Report');
        $this->info(str_repeat("=", 60));
        
        // System Health Check
        $this->checkSystemHealth();
        
        // Database Statistics
        $this->showDatabaseStats();
        
        // Platform Analysis
        $this->showPlatformAnalysis();
        
        if ($detailed) {
            // Plugin Status
            $this->showPluginStatus();
            
            // Recent Activity
            $this->showRecentActivity();
        }
        
        // Performance Metrics
        $this->showPerformanceMetrics();
        
        // Recommendations
        $this->showRecommendations();
        
        $this->info("\n✨ Status report completed!");
        
        return 0;
    }
    
    private function checkSystemHealth()
    {
        $this->info("\n🏥 SYSTEM HEALTH");
        $this->info(str_repeat("-", 40));
        
        // Check services
        try {
            app(\App\Services\TicketScrapingService::class);
            $this->comment("✅ TicketScrapingService: Healthy");
        } catch (Exception $e) {
            $this->error("❌ TicketScrapingService: Error - " . $e->getMessage());
        }
        
        try {
            app(\App\Services\Scraping\PluginBasedScraperManager::class);
            $this->comment("✅ PluginManager: Healthy");
        } catch (Exception $e) {
            $this->error("❌ PluginManager: Error - " . $e->getMessage());
        }
        
        // Check database connection
        try {
            \DB::connection()->getPdo();
            $this->comment("✅ Database: Connected");
        } catch (Exception $e) {
            $this->error("❌ Database: Connection error");
        }
        
        // Check storage permissions
        if (is_writable(storage_path('logs'))) {
            $this->comment("✅ Storage: Writable");
        } else {
            $this->error("❌ Storage: Permission issues");
        }
    }
    
    private function showDatabaseStats()
    {
        $this->info("\n📊 DATABASE STATISTICS");
        $this->info(str_repeat("-", 40));
        
        try {
            $totalTickets = ScrapedTicket::count();
            $availableTickets = ScrapedTicket::where('is_available', true)->count();
            $highDemandTickets = ScrapedTicket::where('is_high_demand', true)->count();
            $recentTickets = ScrapedTicket::where('scraped_at', '>=', now()->subDays(7))->count();
            $todayTickets = ScrapedTicket::where('scraped_at', '>=', now()->subDay())->count();
            
            $this->comment("📈 Total Tickets: {$totalTickets}");
            $this->comment("✅ Available: {$availableTickets}");
            $this->comment("🔥 High Demand: {$highDemandTickets}");
            $this->comment("📅 This Week: {$recentTickets}");
            $this->comment("🕐 Today: {$todayTickets}");
            
            // Calculate health percentage
            $healthPercentage = $totalTickets > 0 ? round(($availableTickets / $totalTickets) * 100, 1) : 0;
            $healthStatus = $healthPercentage >= 80 ? '🟢' : ($healthPercentage >= 50 ? '🟡' : '🔴');
            
            $this->comment("{$healthStatus} Availability Rate: {$healthPercentage}%");
            
        } catch (Exception $e) {
            $this->error("❌ Could not fetch database statistics: " . $e->getMessage());
        }
    }
    
    private function showPlatformAnalysis()
    {
        $this->info("\n🏢 PLATFORM ANALYSIS");
        $this->info(str_repeat("-", 40));
        
        try {
            $platformStats = ScrapedTicket::selectRaw('
                platform,
                COUNT(*) as total,
                COUNT(CASE WHEN is_available = 1 THEN 1 END) as available,
                COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand,
                AVG(min_price) as avg_min_price,
                MAX(scraped_at) as last_scraped
            ')
            ->groupBy('platform')
            ->orderByDesc('total')
            ->get();
            
            if ($platformStats->count() > 0) {
                foreach ($platformStats as $platform) {
                    $avgPrice = $platform->avg_min_price ? '£' . number_format((float)$platform->avg_min_price, 0) : 'N/A';
                    $lastScraped = $platform->last_scraped ? \Carbon\Carbon::parse($platform->last_scraped)->diffForHumans() : 'Never';
                    
                    $this->comment("🏪 {$platform->platform}:");
                    $this->comment("   📊 {$platform->total} total, {$platform->available} available, {$platform->high_demand} high-demand");
                    $this->comment("   💰 Avg price: {$avgPrice} | 🕐 Last scraped: {$lastScraped}");
                }
            } else {
                $this->warn("⚠️ No platform data found");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Could not analyze platforms: " . $e->getMessage());
        }
    }
    
    private function showPluginStatus()
    {
        $this->info("\n🔌 PLUGIN STATUS");
        $this->info(str_repeat("-", 40));
        
        try {
            $manager = app(\App\Services\Scraping\PluginBasedScraperManager::class);
            $plugins = $manager->getPlugins();
            
            $enabled = 0;
            $total = count($plugins);
            
            foreach ($plugins as $name => $plugin) {
                $status = $plugin->isEnabled() ? '🟢 Enabled' : '🔴 Disabled';
                $info = $plugin->getInfo();
                
                $this->comment("🔌 {$name}: {$status}");
                if ($plugin->isEnabled()) {
                    $enabled++;
                }
            }
            
            $this->comment("\n📈 Plugin Summary: {$enabled}/{$total} enabled");
            
        } catch (Exception $e) {
            $this->error("❌ Could not check plugin status: " . $e->getMessage());
        }
    }
    
    private function showRecentActivity()
    {
        $this->info("\n🕐 RECENT ACTIVITY (Last 24h)");
        $this->info(str_repeat("-", 40));
        
        try {
            $recentTickets = ScrapedTicket::where('scraped_at', '>=', now()->subDay())
                ->orderByDesc('scraped_at')
                ->limit(5)
                ->get(['title', 'platform', 'min_price', 'currency', 'scraped_at']);
            
            if ($recentTickets->count() > 0) {
                foreach ($recentTickets as $ticket) {
                    $price = $ticket->min_price ? number_format((float)$ticket->min_price, 0) . ' ' . $ticket->currency : 'N/A';
                    $time = $ticket->scraped_at->diffForHumans();
                    
                    $this->comment("🎫 {$ticket->title}");
                    $this->comment("   💰 {$price} | 🏪 {$ticket->platform} | 🕐 {$time}");
                }
            } else {
                $this->warn("⚠️ No recent activity found");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Could not fetch recent activity: " . $e->getMessage());
        }
    }
    
    private function showPerformanceMetrics()
    {
        $this->info("\n⚡ PERFORMANCE METRICS");
        $this->info(str_repeat("-", 40));
        
        try {
            // Database size estimation
            $avgTicketSize = 2; // KB estimated
            $totalTickets = ScrapedTicket::count();
            $estimatedSize = $totalTickets * $avgTicketSize;
            
            $this->comment("💾 Estimated DB size: {$estimatedSize} KB");
            
            // Memory usage
            $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;
            $this->comment("🧠 Peak memory usage: " . round($memoryUsage, 2) . " MB");
            
            // Check for potential issues
            if ($totalTickets > 10000) {
                $this->warn("⚠️ Large dataset - consider archiving old tickets");
            }
            
            if ($memoryUsage > 128) {
                $this->warn("⚠️ High memory usage detected");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Could not gather performance metrics: " . $e->getMessage());
        }
    }
    
    private function showRecommendations()
    {
        $this->info("\n💡 RECOMMENDATIONS");
        $this->info(str_repeat("-", 40));
        
        $recommendations = [];
        
        try {
            // Check for old tickets
            $oldTickets = ScrapedTicket::where('scraped_at', '<', now()->subDays(30))->count();
            if ($oldTickets > 100) {
                $recommendations[] = "🧹 Consider cleaning up {$oldTickets} old tickets";
            }
            
            // Check availability rate
            $totalTickets = ScrapedTicket::count();
            $availableTickets = ScrapedTicket::where('is_available', true)->count();
            $availabilityRate = $totalTickets > 0 ? ($availableTickets / $totalTickets) * 100 : 0;
            
            if ($availabilityRate < 50) {
                $recommendations[] = "⚡ Low availability rate - consider running fresh scrapes";
            }
            
            // Check for missing data
            $missingPrices = ScrapedTicket::whereNull('min_price')->count();
            if ($missingPrices > 10) {
                $recommendations[] = "💰 {$missingPrices} tickets missing price data";
            }
            
            // General recommendations
            $recommendations[] = "📊 Monitor system with: php artisan scraping:status --detailed";
            $recommendations[] = "🧪 Run tests with: php artisan scraping:test";
            $recommendations[] = "🚀 Enhance system with: php artisan scraping:enhance";
            
            foreach ($recommendations as $recommendation) {
                $this->comment($recommendation);
            }
            
        } catch (Exception $e) {
            $this->error("❌ Could not generate recommendations: " . $e->getMessage());
        }
    }
}
