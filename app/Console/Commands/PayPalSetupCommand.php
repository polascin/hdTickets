<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PayPal\PayPalService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class PayPalSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'paypal:setup 
                           {--verify : Verify PayPal credentials}
                           {--list-webhooks : List existing webhooks}
                           {--create-webhook : Create webhook subscription}
                           {--webhook-url= : Webhook URL for creation}';

    /**
     * The console command description.
     */
    protected $description = 'Setup and manage PayPal integration';

    public function __construct(
        private PayPalService $paypalService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('PayPal Setup and Management Tool');
        $this->info('=====================================');

        // Show current configuration
        $this->showConfiguration();

        // Handle specific options
        if ($this->option('verify')) {
            return $this->verifyCredentials();
        }

        if ($this->option('list-webhooks')) {
            return $this->listWebhooks();
        }

        if ($this->option('create-webhook')) {
            return $this->createWebhook();
        }

        // Default: show help
        $this->showHelp();
        return self::SUCCESS;
    }

    /**
     * Show current PayPal configuration
     */
    private function showConfiguration(): void
    {
        $this->info("\nCurrent Configuration:");
        $this->info("=====================");
        
        $mode = config('services.paypal.mode', 'sandbox');
        $this->line("Mode: <comment>{$mode}</comment>");
        
        $clientId = config("services.paypal.{$mode}.client_id");
        $clientSecret = config("services.paypal.{$mode}.client_secret");
        
        $this->line("Client ID: " . ($clientId ? '<info>✓ Configured</info>' : '<error>✗ Not configured</error>'));
        $this->line("Client Secret: " . ($clientSecret ? '<info>✓ Configured</info>' : '<error>✗ Not configured</error>'));
        
        $webhookId = config('services.paypal.webhook_id');
        $this->line("Webhook ID: " . ($webhookId ? '<info>✓ Configured</info>' : '<error>✗ Not configured</error>'));
        
        $receiverEmail = config('services.paypal.receiver_email');
        $this->line("Receiver Email: <comment>{$receiverEmail}</comment>");
        
        $this->newLine();
    }

    /**
     * Verify PayPal credentials
     */
    private function verifyCredentials(): int
    {
        $this->info("Verifying PayPal Credentials...");
        
        try {
            // Test creating a simple order
            $testOrder = $this->paypalService->createOrder(1.00, 'USD', [
                'test' => true,
                'description' => 'Credential verification test',
            ]);
            
            if (isset($testOrder['id'])) {
                $this->info('<info>✓ Credentials verified successfully!</info>');
                $this->line("Test Order ID: <comment>{$testOrder['id']}</comment>");
                
                if (isset($testOrder['approve_link'])) {
                    $this->line("Test Approval URL: {$testOrder['approve_link']}");
                }
                
                return self::SUCCESS;
            } else {
                $this->error('✗ Credential verification failed - Invalid response');
                return self::FAILURE;
            }
        } catch (Exception $e) {
            $this->error('✗ Credential verification failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * List existing webhooks
     */
    private function listWebhooks(): int
    {
        $this->info("Listing PayPal Webhooks...");
        
        try {
            // Get webhook list from PayPal API
            $client = $this->paypalService->getClient();
            $webhooksController = new \PaypalServerSdkLib\Controllers\WebhooksController($client);
            
            $response = $webhooksController->webhooksList();
            $webhooks = json_decode($response->getBody(), true);
            
            if (empty($webhooks['webhooks'])) {
                $this->warn('No webhooks found.');
                return self::SUCCESS;
            }
            
            $this->info("Found " . count($webhooks['webhooks']) . " webhook(s):");
            $this->newLine();
            
            $headers = ['ID', 'URL', 'Status', 'Events'];
            $rows = [];
            
            foreach ($webhooks['webhooks'] as $webhook) {
                $events = collect($webhook['event_types'] ?? [])->pluck('name')->join(', ');
                $rows[] = [
                    substr($webhook['id'], 0, 20) . '...',
                    $webhook['url'],
                    $webhook['status'] ?? 'Unknown',
                    strlen($events) > 50 ? substr($events, 0, 47) . '...' : $events,
                ];
            }
            
            $this->table($headers, $rows);
            
            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to list webhooks: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Create webhook subscription
     */
    private function createWebhook(): int
    {
        $webhookUrl = $this->option('webhook-url') ?: $this->ask('Enter webhook URL', url('/webhooks/paypal'));
        
        if (!$webhookUrl) {
            $this->error('Webhook URL is required');
            return self::FAILURE;
        }
        
        $this->info("Creating PayPal Webhook...");
        $this->line("URL: <comment>{$webhookUrl}</comment>");
        
        try {
            $client = $this->paypalService->getClient();
            $webhooksController = new \PaypalServerSdkLib\Controllers\WebhooksController($client);
            
            $webhookRequest = [
                'url' => $webhookUrl,
                'event_types' => [
                    ['name' => 'BILLING.SUBSCRIPTION.CREATED'],
                    ['name' => 'BILLING.SUBSCRIPTION.ACTIVATED'],
                    ['name' => 'BILLING.SUBSCRIPTION.CANCELLED'],
                    ['name' => 'BILLING.SUBSCRIPTION.SUSPENDED'],
                    ['name' => 'BILLING.SUBSCRIPTION.EXPIRED'],
                    ['name' => 'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED'],
                    ['name' => 'BILLING.SUBSCRIPTION.PAYMENT.FAILED'],
                    ['name' => 'PAYMENT.CAPTURE.COMPLETED'],
                    ['name' => 'PAYMENT.CAPTURE.DENIED'],
                    ['name' => 'PAYMENT.CAPTURE.REFUNDED'],
                ],
            ];
            
            $response = $webhooksController->webhooksPost($webhookRequest);
            $webhook = json_decode($response->getBody(), true);
            
            if (isset($webhook['id'])) {
                $this->info('<info>✓ Webhook created successfully!</info>');
                $this->line("Webhook ID: <comment>{$webhook['id']}</comment>");
                $this->newLine();
                
                $this->warn('Important: Add this webhook ID to your .env file:');
                $this->line("PAYPAL_WEBHOOK_ID={$webhook['id']}");
                
                // Update config in memory (won't persist to .env)
                Config::set('services.paypal.webhook_id', $webhook['id']);
                
                return self::SUCCESS;
            } else {
                $this->error('✗ Webhook creation failed - Invalid response');
                return self::FAILURE;
            }
        } catch (Exception $e) {
            $this->error('Failed to create webhook: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Show command help
     */
    private function showHelp(): void
    {
        $this->info("Available Options:");
        $this->info("==================");
        $this->line("--verify              Verify PayPal API credentials");
        $this->line("--list-webhooks       List existing webhooks");
        $this->line("--create-webhook      Create a new webhook subscription");
        $this->line("--webhook-url=URL     Specify webhook URL for creation");
        $this->newLine();
        
        $this->info("Examples:");
        $this->line("php artisan paypal:setup --verify");
        $this->line("php artisan paypal:setup --list-webhooks");
        $this->line("php artisan paypal:setup --create-webhook");
        $this->line("php artisan paypal:setup --create-webhook --webhook-url=https://yourdomain.com/webhooks/paypal");
    }
}