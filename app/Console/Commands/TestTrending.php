<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\TicketScrapingController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestTrending extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:trending';

    /**
     * The console command description.
     */
    protected $description = 'Test the trending endpoint functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing TicketScrapingController trending method...');
        
        try {
            // Create controller instance using service container
            $controller = app(TicketScrapingController::class);
            
            // Create a mock request
            $request = Request::create('/test', 'GET');
            app()->instance('request', $request);
            
            // Test the trending method
            $this->info('Testing trending() method...');
            $response = $controller->trending($request);
            
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $this->info("Response type: JsonResponse");
                $this->info("Status code: " . $response->getStatusCode());
                $this->info("Content: " . $response->getContent());
            } else {
                $this->info("Response type: " . get_class($response));
                $this->line("Response: " . print_r($response, true));
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
            $this->error("Stack trace:");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
