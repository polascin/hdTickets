<?php declare(strict_types=1);

namespace Tests\Performance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Override;
use Tests\TestCase;

use function count;
use function strlen;

class LoginPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $testUser;

    private string $testPassword = 'TestP@ssw0rd123!';

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create([
            'email'     => 'performance@test.com',
            'password'  => Hash::make($this->testPassword),
            'is_active' => TRUE,
            'role'      => 'customer',
        ]);
    }

    /**
     * @test
     */
    public function test_login_page_load_performance(): void
    {
        $startTime = microtime(TRUE);

        $response = $this->get('/login');

        $endTime = microtime(TRUE);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);

        // Login page should load within 500ms (adjust threshold as needed)
        $this->assertLessThan(500, $loadTime, "Login page took {$loadTime}ms to load, which exceeds 500ms threshold");

        echo "\nLogin page load time: " . round($loadTime, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_login_authentication_performance(): void
    {
        $startTime = microtime(TRUE);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $endTime = microtime(TRUE);
        $authTime = ($endTime - $startTime) * 1000;

        $response->assertRedirect('/dashboard');

        // Authentication should complete within 1000ms
        $this->assertLessThan(1000, $authTime, "Authentication took {$authTime}ms, which exceeds 1000ms threshold");

        echo "\nAuthentication time: " . round($authTime, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_concurrent_login_performance(): void
    {
        $users = User::factory()->count(10)->create([
            'password'  => Hash::make($this->testPassword),
            'is_active' => TRUE,
            'role'      => 'customer',
        ]);

        $startTime = microtime(TRUE);
        $responses = [];

        // Simulate concurrent login attempts
        foreach ($users as $user) {
            $responses[] = $this->post('/login', [
                'email'    => $user->email,
                'password' => $this->testPassword,
                'website'  => '',
            ]);
        }

        $endTime = microtime(TRUE);
        $totalTime = ($endTime - $startTime) * 1000;
        $averageTime = $totalTime / count($users);

        // All logins should succeed
        foreach ($responses as $response) {
            $response->assertRedirect('/dashboard');
        }

        // Average login time should be reasonable under load
        $this->assertLessThan(2000, $averageTime, "Average login time under concurrent load was {$averageTime}ms");

        echo "\nConcurrent login average time: " . round($averageTime, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_database_query_performance_during_login(): void
    {
        DB::enableQueryLog();

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertRedirect('/dashboard');

        // Should not exceed reasonable number of queries
        $this->assertLessThan(10, count($queries), 'Login process executed ' . count($queries) . ' queries, which may be excessive');

        // Check for N+1 queries or slow queries
        $slowQueries = array_filter($queries, function (array $query): bool {
            return $query['time'] > 100; // 100ms threshold
        });

        $this->assertEmpty($slowQueries, 'Found slow queries during login: ' . json_encode($slowQueries));

        echo "\nTotal queries during login: " . count($queries) . "\n";
    }

    /**
     * @test
     */
    public function test_memory_usage_during_login(): void
    {
        $memoryBefore = memory_get_usage(TRUE);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $memoryAfter = memory_get_usage(TRUE);
        $memoryUsed = $memoryAfter - $memoryBefore;

        $response->assertRedirect('/dashboard');

        // Memory usage should be reasonable (adjust threshold as needed)
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed, 'Login process used ' . round($memoryUsed / 1024 / 1024, 2) . 'MB of memory');

        echo "\nMemory used during login: " . round($memoryUsed / 1024 / 1024, 2) . "MB\n";
    }

    /**
     * @test
     */
    public function test_cache_performance_during_login(): void
    {
        // Pre-warm cache with user data
        Cache::put("user_cache_{$this->testUser->id}", $this->testUser, 3600);

        $startTime = microtime(TRUE);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $endTime = microtime(TRUE);
        $timeWithCache = ($endTime - $startTime) * 1000;

        $response->assertRedirect('/dashboard');

        // Clear cache and test without it
        Cache::forget("user_cache_{$this->testUser->id}");

        $startTime = microtime(TRUE);

        // Create new user to avoid authentication state conflicts
        $newUser = User::factory()->create([
            'password'  => Hash::make($this->testPassword),
            'is_active' => TRUE,
            'role'      => 'customer',
        ]);

        $response = $this->post('/login', [
            'email'    => $newUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $endTime = microtime(TRUE);
        $timeWithoutCache = ($endTime - $startTime) * 1000;

        $response->assertRedirect('/dashboard');

        echo "\nLogin with cache: " . round($timeWithCache, 2) . "ms\n";
        echo 'Login without cache: ' . round($timeWithoutCache, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_rate_limiting_performance(): void
    {
        $attempts = [];

        // Test multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $startTime = microtime(TRUE);

            $response = $this->post('/login', [
                'email'    => $this->testUser->email,
                'password' => 'wrong-password',
                'website'  => '',
            ]);

            $endTime = microtime(TRUE);
            $attempts[] = ($endTime - $startTime) * 1000;

            $response->assertSessionHasErrors();
        }

        // Rate limiting should not significantly degrade performance
        $averageAttemptTime = array_sum($attempts) / count($attempts);
        $this->assertLessThan(1500, $averageAttemptTime, "Average failed login attempt took {$averageAttemptTime}ms");

        echo "\nAverage failed login attempt time: " . round($averageAttemptTime, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_session_handling_performance(): void
    {
        $startTime = microtime(TRUE);

        // Test session creation during login
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $endTime = microtime(TRUE);
        $sessionTime = ($endTime - $startTime) * 1000;

        $response->assertRedirect('/dashboard');

        // Test session access after login
        $startTime = microtime(TRUE);

        $dashboardResponse = $this->get('/dashboard');

        $endTime = microtime(TRUE);
        $sessionAccessTime = ($endTime - $startTime) * 1000;

        $dashboardResponse->assertStatus(200);

        echo "\nSession creation time: " . round($sessionTime, 2) . "ms\n";
        echo 'Session access time: ' . round($sessionAccessTime, 2) . "ms\n";

        // Session operations should be fast
        $this->assertLessThan(1000, $sessionTime, "Session creation took {$sessionTime}ms");
        $this->assertLessThan(500, $sessionAccessTime, "Session access took {$sessionAccessTime}ms");
    }

    /**
     * @test
     */
    public function test_two_factor_auth_performance(): void
    {
        // Enable 2FA for user
        $this->testUser->update([
            'two_factor_secret'  => 'test-secret-key-for-2fa',
            'two_factor_enabled' => TRUE,
        ]);

        $startTime = microtime(TRUE);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $endTime = microtime(TRUE);
        $twoFactorTime = ($endTime - $startTime) * 1000;

        // Should redirect to 2FA challenge
        $response->assertRedirect('/two-factor-challenge');

        // 2FA-enabled login should still be reasonably fast
        $this->assertLessThan(1500, $twoFactorTime, "2FA login took {$twoFactorTime}ms");

        echo "\n2FA-enabled login time: " . round($twoFactorTime, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_activity_logging_performance(): void
    {
        // Enable activity logging
        activity()->enableLogging();

        $startTime = microtime(TRUE);

        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);

        $endTime = microtime(TRUE);
        $loggingTime = ($endTime - $startTime) * 1000;

        $response->assertRedirect('/dashboard');

        // Activity logging should not significantly impact performance
        $this->assertLessThan(1200, $loggingTime, "Login with activity logging took {$loggingTime}ms");

        // Verify activity was logged
        $this->assertDatabaseHas('activity_log', [
            'subject_id'  => $this->testUser->id,
            'description' => 'User logged in successfully',
        ]);

        echo "\nLogin with activity logging time: " . round($loggingTime, 2) . "ms\n";
    }

    /**
     * @test
     */
    public function test_password_hashing_performance(): void
    {
        $passwords = [
            'SimplePass123!',
            'ComplexP@ssw0rd!WithM0re$pecialChars&Numb3rs',
            'VeryLongPasswordWithManyCharacters123456789!@#$%^&*()',
        ];

        foreach ($passwords as $password) {
            $startTime = microtime(TRUE);

            $hashedPassword = Hash::make($password);

            $endTime = microtime(TRUE);
            $hashTime = ($endTime - $startTime) * 1000;

            // Verify hash is correct
            $this->assertTrue(Hash::check($password, $hashedPassword));

            // Hashing should complete in reasonable time
            $this->assertLessThan(1000, $hashTime, 'Hashing password of length ' . strlen($password) . " took {$hashTime}ms");

            echo "\nPassword hashing time (" . strlen($password) . ' chars): ' . round($hashTime, 2) . "ms\n";
        }
    }

    /**
     * @test
     */
    public function test_login_response_size(): void
    {
        $response = $this->get('/login');

        $content = $response->getContent();
        $contentSize = strlen($content);

        // Response size should be reasonable (not including external assets)
        $this->assertLessThan(50000, $contentSize, "Login page HTML size is {$contentSize} bytes, which may be excessive");

        echo "\nLogin page content size: " . round($contentSize / 1024, 2) . "KB\n";
    }

    /**
     * @test
     */
    public function test_core_web_vitals_simulation(): void
    {
        // Simulate Core Web Vitals measurements

        // Largest Contentful Paint (LCP) - simulated
        $startTime = microtime(TRUE);
        $response = $this->get('/login');
        $endTime = microtime(TRUE);
        $lcp = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // LCP should be under 2.5s (2500ms) for good performance
        $this->assertLessThan(2500, $lcp, "Simulated LCP was {$lcp}ms, exceeding 2500ms threshold");

        // First Input Delay (FID) - simulate form interaction
        $startTime = microtime(TRUE);
        $response = $this->post('/login', [
            'email'    => $this->testUser->email,
            'password' => $this->testPassword,
            'website'  => '',
        ]);
        $endTime = microtime(TRUE);
        $fid = ($endTime - $startTime) * 1000;

        $response->assertRedirect('/dashboard');

        // FID should be under 100ms for good performance
        $this->assertLessThan(100, $fid, "Simulated FID was {$fid}ms, exceeding 100ms threshold");

        echo "\nSimulated LCP: " . round($lcp, 2) . "ms\n";
        echo 'Simulated FID: ' . round($fid, 2) . "ms\n";
    }

    #[Override]
    protected function tearDown(): void
    {
        // Clean up any performance test artifacts
        Cache::flush();

        parent::tearDown();
    }
}
