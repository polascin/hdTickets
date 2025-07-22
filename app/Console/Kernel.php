<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
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
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
