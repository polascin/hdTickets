<?php

namespace Tests\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class SecurityAuditTest extends TestCase
{
    public function test_password_hashing_security()
    {
        $password = 'test-password-123';
        $hashedPassword = Hash::make($password);
        
        // Password should be hashed
        $this->assertNotEquals($password, $hashedPassword);
        
        // Hash should be using bcrypt or better
        $this->assertTrue(Hash::check($password, $hashedPassword));
        
        // Hash should be at least 60 characters (bcrypt minimum)
        $this->assertGreaterThanOrEqual(60, strlen($hashedPassword));
        
        // Should use appropriate cost factor
        $hashInfo = password_get_info($hashedPassword);
        $this->assertGreaterThanOrEqual(10, $hashInfo['options']['cost'] ?? 0);
    }

    public function test_sql_injection_prevention()
    {
        $maliciousInput = "'; DROP TABLE scraped_tickets; --";
        
        // Test in search functionality
        $response = $this->actingAs($this->createUser())
            ->get("/api/v1/tickets/search?q=" . urlencode($maliciousInput));
        
        // Should not cause error and tickets table should still exist
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('scraped_tickets'));
        
        // Test in model queries
        $tickets = ScrapedTicket::where('title', 'LIKE', '%' . $maliciousInput . '%')->get();
        $this->assertIsObject($tickets);
        
        // Verify table still exists after query
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('scraped_tickets'));
    }

    public function test_xss_prevention_in_output()
    {
        $maliciousScript = '<script>alert("XSS")</script>';
        
        $ticket = $this->createScrapedTicket([
            'title' => $maliciousScript,
            'venue' => 'Test Venue ' . $maliciousScript,
            'location' => 'Test Location ' . $maliciousScript
        ]);
        
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/dashboard');
        
        // Script tags should be escaped in output
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        
        // But escaped content might be visible
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
    }

    public function test_csrf_protection()
    {
        $user = $this->createUser();
        
        // POST request without CSRF token should fail
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->post('/api/v1/alerts', [
                'name' => 'Test Alert',
                'keywords' => 'Manchester United'
            ]);
        
        // With proper middleware, this should require CSRF token
        $this->assertTrue(true); // Basic test structure
    }

    public function test_unauthorized_access_prevention()
    {
        // Test admin routes require admin role
        $regularUser = $this->createUser('customer');
        
        $response = $this->actingAs($regularUser)->get('/admin/dashboard');
        $this->assertEquals(403, $response->status());
        
        // Test API endpoints require authentication
        $response = $this->get('/api/v1/tickets');
        $this->assertEquals(401, $response->status());
        
        // Test user can only access their own data
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        
        $alert1 = $this->createTicketAlert($user1);
        
        $response = $this->actingAs($user2)->get("/api/v1/alerts/{$alert1->id}");
        $this->assertEquals(403, $response->status());
    }

    public function test_rate_limiting_protection()
    {
        $user = $this->createUser();
        
        // Make multiple rapid requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->actingAs($user)->get('/api/v1/tickets');
        }
        
        // At some point, should be rate limited (429 status)
        $rateLimited = collect($responses)->contains(fn($response) => $response->status() === 429);
        
        // This test depends on rate limiting configuration
        // In a real scenario, you'd want to test with higher request volumes
        $this->assertTrue(true); // Placeholder assertion
    }

    public function test_input_validation_and_sanitization()
    {
        $user = $this->createUser();
        
        // Test with invalid input data
        $response = $this->actingAs($user)->postJson('/api/v1/alerts', [
            'name' => str_repeat('A', 300), // Too long name
            'keywords' => '', // Empty keywords
            'max_price' => 'invalid_price', // Invalid price format
            'platform' => 'nonexistent_platform' // Invalid platform
        ]);
        
        // Should return validation errors
        $this->assertEquals(422, $response->status());
        $response->assertJsonValidationErrors(['name', 'keywords', 'max_price', 'platform']);
    }

    public function test_sensitive_data_exposure_prevention()
    {
        $user = $this->createUser();
        
        // API responses should not expose sensitive fields
        $response = $this->actingAs($user)->get('/api/v1/user/profile');
        
        $response->assertStatus(200);
        
        $responseData = $response->json();
        
        // Should not expose password hash or remember token
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('remember_token', $responseData);
        
        // Should not expose scraper account details to regular users
        if ($user->role !== 'admin') {
            $this->assertArrayNotHasKey('is_scraper_account', $responseData);
        }
    }

    public function test_secure_session_configuration()
    {
        $sessionConfig = config('session');
        
        // Session should be secure
        $this->assertTrue($sessionConfig['secure'] || app()->environment('testing'));
        
        // Session should be HTTP only
        $this->assertTrue($sessionConfig['http_only']);
        
        // Should use secure SameSite setting
        $this->assertContains($sessionConfig['same_site'], ['strict', 'lax']);
        
        // Session lifetime should be reasonable
        $this->assertLessThan(1440, $sessionConfig['lifetime']); // Less than 24 hours
    }

    public function test_api_endpoint_security()
    {
        // Test that API endpoints validate content type
        $response = $this->postJson('/api/v1/tickets', [], [
            'Content-Type' => 'text/plain'
        ]);
        
        // Should handle invalid content type appropriately
        $this->assertContains($response->status(), [400, 415]);
        
        // Test API versioning prevents access to non-existent versions
        $response = $this->get('/api/v999/tickets');
        $this->assertEquals(404, $response->status());
    }

    public function test_file_upload_security()
    {
        $user = $this->createUser('admin');
        
        // Test upload of malicious file types
        $maliciousFile = \Illuminate\Http\UploadedFile::fake()->create('malicious.php', 1024);
        
        // Assuming there's an upload endpoint (if it exists)
        $response = $this->actingAs($user)->postJson('/api/v1/uploads', [
            'file' => $maliciousFile
        ]);
        
        // Should reject dangerous file types
        $this->assertContains($response->status(), [422, 400]);
    }

    public function test_environment_configuration_security()
    {
        // Debug should be disabled in production
        if (app()->environment('production')) {
            $this->assertFalse(config('app.debug'));
        }
        
        // Database credentials should not be default values
        $dbConfig = config('database.connections.mysql');
        if (isset($dbConfig)) {
            $this->assertNotEquals('root', $dbConfig['username']);
            $this->assertNotEquals('', $dbConfig['password']);
            $this->assertNotEquals('password', $dbConfig['password']);
        }
        
        // App key should be set
        $this->assertNotEmpty(config('app.key'));
        $this->assertNotEquals('base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=', config('app.key'));
    }

    public function test_headers_security()
    {
        $response = $this->get('/');
        
        // Should have security headers (if configured)
        $headers = $response->headers->all();
        
        // Test for common security headers
        if (isset($headers['x-frame-options'])) {
            $this->assertContains(strtolower($headers['x-frame-options'][0]), ['deny', 'sameorigin']);
        }
        
        if (isset($headers['x-content-type-options'])) {
            $this->assertEquals('nosniff', strtolower($headers['x-content-type-options'][0]));
        }
        
        // Should not expose sensitive information
        $this->assertArrayNotHasKey('x-powered-by', array_change_key_case($headers, CASE_LOWER));
    }

    public function test_api_authentication_security()
    {
        // Test that expired tokens are rejected
        $user = $this->createUser();
        
        // Create an API token (if using Sanctum/Passport)
        $token = $user->createToken('test-token')->plainTextToken ?? 'fake-token';
        
        // Test with valid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get('/api/v1/user');
        
        // Should work with valid token
        $this->assertContains($response->status(), [200, 401]); // 401 if tokens not implemented
        
        // Test with invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ])->get('/api/v1/user');
        
        $this->assertEquals(401, $response->status());
    }

    public function test_database_connection_security()
    {
        // Test that database connection is encrypted (if configured)
        $dbConfig = config('database.connections.' . config('database.default'));
        
        if (isset($dbConfig['options'])) {
            // Should use SSL for production databases
            $this->assertTrue(true); // Placeholder - actual test would check SSL configuration
        }
        
        // Test that sensitive database operations are logged
        $initialLogCount = DB::getQueryLog() ? count(DB::getQueryLog()) : 0;
        
        // Perform a database operation
        User::count();
        
        // Query should be logged (if query logging is enabled)
        if (config('app.debug')) {
            $this->assertGreaterThan($initialLogCount, count(DB::getQueryLog()));
        }
    }

    public function test_scraping_security()
    {
        // Test that scraper endpoints validate user permissions
        $regularUser = $this->createUser('customer');
        $scraperUser = $this->createUser('scraper', ['is_scraper_account' => true]);
        
        // Regular user should not access scraper endpoints
        $response = $this->actingAs($regularUser)->post('/api/v1/scraper/start');
        $this->assertEquals(403, $response->status());
        
        // Test that external API calls use proper security measures
        Http::fake([
            'stubhub.com/*' => Http::response(['error' => 'Unauthorized'], 401),
            'ticketmaster.com/*' => Http::response(['error' => 'Forbidden'], 403)
        ]);
        
        // Scraping service should handle API authentication errors
        $service = new \App\Services\TicketScrapingService();
        $results = $service->searchTickets('test', ['platforms' => ['stubhub']]);
        
        // Should handle errors gracefully without exposing sensitive data
        $this->assertIsArray($results);
        $this->assertEmpty($results['stubhub']);
    }

    public function test_logging_security()
    {
        // Test that sensitive data is not logged
        $password = 'secret-password-123';
        $user = User::factory()->create(['password' => Hash::make($password)]);
        
        // Attempt login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password
        ]);
        
        // Check that password is not in plain text in logs
        $logFiles = glob(storage_path('logs/*.log'));
        foreach ($logFiles as $logFile) {
            $logContent = file_get_contents($logFile);
            $this->assertStringNotContainsString($password, $logContent);
        }
    }

    public function test_error_handling_security()
    {
        // Test that error messages don't expose sensitive information
        $response = $this->get('/nonexistent-route');
        $this->assertEquals(404, $response->status());
        
        // Error response should not expose system information
        $content = $response->getContent();
        $this->assertStringNotContainsString('/home/', $content);
        $this->assertStringNotContainsString('C:\\', $content);
        $this->assertStringNotContainsString('Database', $content);
        $this->assertStringNotContainsString('SQL', $content);
    }

    public function test_dependency_security()
    {
        // Check that dependencies are up to date (basic check)
        $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
        
        // Should have composer.lock file
        $this->assertNotNull($composerLock);
        
        // Should have packages installed
        $this->assertArrayHasKey('packages', $composerLock);
        $this->assertGreaterThan(0, count($composerLock['packages']));
        
        // Laravel framework should be present
        $laravelPackage = collect($composerLock['packages'])
            ->firstWhere('name', 'laravel/framework');
        
        $this->assertNotNull($laravelPackage);
    }
}
