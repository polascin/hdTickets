<?php declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    /**
     * Schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ticket scraping schedules

        // Check alerts every 15 minutes during peak hours (9 AM - 11 PM)
        $schedule->command('tickets:scrape --check-alerts')
            ->cron('*/15 9-23 * * *')
            ->timezone('Europe/London')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/ticket-alerts.log'));

        // Scrape Manchester United tickets every 30 minutes during peak hours
        $schedule->command('tickets:scrape --manchester-united')
            ->cron('*/30 9-23 * * *')
            ->timezone('Europe/London')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/manchester-united-scraping.log'));

        // Scrape high-demand sports tickets every hour during peak hours
        $schedule->command('tickets:scrape --high-demand')
            ->hourly()
            ->between('09:00', '23:00')
            ->timezone('Europe/London')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/high-demand-scraping.log'));

        // Full scraping run twice daily (morning and evening)
        $schedule->command('tickets:scrape')
            ->twiceDaily(9, 19) // 9 AM and 7 PM
            ->timezone('Europe/London')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/full-scraping.log'));

        // Clean up old scraped tickets (older than 7 days) daily at 3 AM
        $schedule->command('model:prune', ['--model' => 'App\\Models\\ScrapedTicket'])
            ->dailyAt('03:00')
            ->timezone('Europe/London');

        // Performance optimization schedules

        // Warm cache every 2 hours during active hours
        $schedule->command('cache:warm')
            ->cron('0 */2 6-23 * * *') // Every 2 hours from 6 AM to 11 PM
            ->timezone('Europe/London')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cache-warming.log'));

        // Database optimization daily at 2 AM
        $schedule->command('db:optimize')
            ->dailyAt('02:00')
            ->timezone('Europe/London')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/db-optimization.log'));

        // Cache statistics every hour for monitoring
        $schedule->call(function (): void {
            $service = app(\App\Services\PerformanceOptimizationService::class);
            $stats = $service->getCacheStatistics();
            Log::info('Cache Statistics', $stats);
        })->hourly()->name('cache-stats');
    }

    /**
     * Register the commands for the application.
     */
    /**
     * Commands
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
