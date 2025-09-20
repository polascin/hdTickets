<?php declare(strict_types=1);
/**
 * Fix Remaining Parse Errors Script
 * This script fixes all remaining parse errors in the Laravel application
 */

// List of files and patterns to fix
$fixes = [
    // Fix TickPickController.php similar to StubHub and Viagogo controllers
    'app/Http/Controllers/Api/TickPickController.php' => [
        // Fix malformed try-catch blocks
        'search' => [
            '/\}\s*\}\s*catch\s*\(\s*\\\\Exception\s*\$e\)\s*\{\s*throw\s*\$e;\s*\}\s*catch/s',
            '/return\s*new\s*\$1::/s',
            '/\}\s*catch\s*\(\s*\\\\Exception\s*\$e\)\s*\{\s*throw\s*\$e;\s*\}([^}])/s',
        ],
        'replace' => [
            '} catch',
            'return new \\\\App\\\\Models\\\\Ticket(',
            '} \\1',
        ],
    ],

    // Fix AgentDashboardController.php
    'app/Http/Controllers/Admin/AgentDashboardController.php' => [
        'search' => [
            '/\}\s*\}\s*catch\s*\(\s*\\\\Exception\s*\$e\)\s*\{\s*throw\s*\$e;\s*\}\s*catch/s',
        ],
        'replace' => [
            '} catch',
        ],
    ],
];

echo "Starting to fix remaining parse errors...\n";

foreach ($fixes as $file => $patterns) {
    $filePath = "/var/www/hdtickets/{$file}";

    if (!file_exists($filePath)) {
        echo "File not found: {$file}\n";

        continue;
    }

    echo "Processing: {$file}\n";
    $content = file_get_contents($filePath);

    // Apply regex patterns
    foreach ($patterns['search'] as $index => $pattern) {
        $replacement = $patterns['replace'][$index];
        $newContent = preg_replace($pattern, $replacement, $content);
        if ($newContent !== NULL) {
            $content = $newContent;
        }
    }

    // Save the fixed content
    file_put_contents($filePath, $content);
    echo "Fixed: {$file}\n";
}

// Create clean versions of remaining problematic controllers
$cleanControllers = [
    'TickPickController' => [
        'path'     => 'app/Http/Controllers/Api/TickPickController.php',
        'class'    => 'TickPickController',
        'platform' => 'tickpick',
        'client'   => 'TickPickClient',
        'regex'    => 'tickpick\.com',
    ],
];

foreach ($cleanControllers as $controllerName => $config) {
    echo "Creating clean {$controllerName}...\n";

    $template = "<?php declare(strict_types=1);

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Services\\TicketApis\\{$config['client']};
use Exception;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Validator;

use function count;

class {$config['class']} extends Controller
{
    public function __construct() {
        \$this->middleware('api.rate_limit:{$config['platform']},30,1')->only(['search', 'getEventDetails']);
        \$this->middleware('api.rate_limit:{$config['platform']}_import,10,1')->only(['import', 'importUrls']);
        \$this->middleware('auth:sanctum')->only(['import', 'importUrls']);
        \$this->middleware('role:agent,admin')->only(['import', 'importUrls']);
    }

    public function search(Request \$request): JsonResponse
    {
        \$validator = Validator::make(\$request->all(), [
            'keyword'  => 'required|string|min:2|max:100',
            'location' => 'nullable|string|max:100',
            'limit'    => 'nullable|integer|min:1|max:100',
        ]);

        if (\$validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => \$validator->errors(),
            ], 422);
        }

        \$keyword = \$request->input('keyword');
        \$location = \$request->input('location', '');
        \$limit = \$request->input('limit', 20);

        try {
            \$client = new {$config['client']}([
                'enabled' => TRUE,
                'api_key' => config('services.{$config['platform']}.api_key'),
                'timeout' => 30,
            ]);

            \$results = \$client->scrapeSearchResults(\$keyword, \$location, \$limit);

            return response()->json([
                'success' => TRUE,
                'data'    => \$results,
                'meta'    => [
                    'keyword'       => \$keyword,
                    'location'      => \$location,
                    'total_results' => count(\$results),
                    'limit'         => \$limit,
                ],
            ]);
        } catch (Exception \$e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Search failed: ' . \$e->getMessage(),
            ], 500);
        }
    }

    public function getEventDetails(Request \$request): JsonResponse
    {
        \$validator = Validator::make(\$request->all(), [
            'url' => 'required|url|regex:/{$config['regex']}/',
        ]);

        if (\$validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => \$validator->errors(),
            ], 422);
        }

        \$url = \$request->input('url');

        try {
            \$client = new {$config['client']}([
                'enabled' => TRUE,
                'api_key' => config('services.{$config['platform']}.api_key'),
                'timeout' => 30,
            ]);

            \$eventDetails = \$client->scrapeEventDetails(\$url);

            if (empty(\$eventDetails)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'No event details found for the provided URL',
                ], 404);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => \$eventDetails,
            ]);
        } catch (Exception \$e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to get event details: ' . \$e->getMessage(),
            ], 500);
        }
    }

    public function import(Request \$request): JsonResponse
    {
        \$validator = Validator::make(\$request->all(), [
            'keyword'  => 'required|string|min:2|max:100',
            'location' => 'nullable|string|max:100',
            'limit'    => 'nullable|integer|min:1|max:50',
        ]);

        if (\$validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => \$validator->errors(),
            ], 422);
        }

        \$keyword = \$request->input('keyword');
        \$location = \$request->input('location', '');
        \$limit = \$request->input('limit', 10);

        try {
            \$client = new {$config['client']}([
                'enabled' => TRUE,
                'api_key' => config('services.{$config['platform']}.api_key'),
                'timeout' => 30,
            ]);

            \$events = \$client->scrapeSearchResults(\$keyword, \$location, \$limit);

            if (empty(\$events)) {
                return response()->json([
                    'success'  => FALSE,
                    'message'  => 'No events found for the search criteria',
                    'imported' => 0,
                ], 404);
            }

            \$imported = 0;
            \$errors = [];

            foreach (\$events as \$event) {
                try {
                    if (\$this->importEventAsTicket(\$event)) {
                        \$imported++;
                    }
                    usleep(500000);
                } catch (Exception \$e) {
                    \$errors[] = [
                        'event' => \$event['name'] ?? 'Unknown',
                        'error' => \$e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success'     => TRUE,
                'total_found' => count(\$events),
                'imported'    => \$imported,
                'errors'      => \$errors,
                'message'     => \"Successfully imported {\$imported} out of \" . count(\$events) . ' events',
            ]);
        } catch (Exception \$e) {
            return response()->json([
                'success'  => FALSE,
                'message'  => 'Import failed: ' . \$e->getMessage(),
                'imported' => 0,
            ], 500);
        }
    }

    private function importEventAsTicket(array \$eventData): bool
    {
        try {
            \$existingTicket = \\App\\Models\\Ticket::where('platform', '{$config['platform']}')
                ->where('external_id', \$eventData['id'] ?? NULL)
                ->first();

            if (\$existingTicket) {
                return FALSE;
            }

            \$ticket = new \\App\\Models\\Ticket([
                'platform'     => '{$config['platform']}',
                'external_id'  => \$eventData['id'] ?? NULL,
                'title'        => \$eventData['name'] ?? 'Unknown Event',
                'price'        => \$eventData['price'] ?? 0.00,
                'currency'     => \$eventData['currency'] ?? 'USD',
                'venue'        => \$eventData['venue'] ?? '',
                'event_date'   => \$eventData['date'] ?? now(),
                'category'     => \$eventData['category'] ?? 'General',
                'description'  => \$eventData['description'] ?? '',
                'url'          => \$eventData['url'] ?? '',
                'status'       => 'available',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            \$ticket->save();
            return TRUE;
        } catch (Exception \$e) {
            \\Illuminate\\Support\\Facades\\Log::error('Failed to import {$config['platform']} event as ticket', [
                'event_data' => \$eventData,
                'error'      => \$e->getMessage(),
            ]);
            return FALSE;
        }
    }
}
";

    file_put_contents("/var/www/hdtickets/{$config['path']}", $template);
    echo "Created clean {$controllerName}\n";
}

echo "Parse error fixes completed!\n";

// Run PHPStan to check results
echo "Running PHPStan to verify fixes...\n";
system('cd /var/www/hdtickets && vendor/bin/phpstan analyse --level=1 --error-format=table | tail -10');
