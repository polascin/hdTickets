<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LoginHistory;
use App\Models\UserSession;
use App\Services\TwoFactorAuthService;
use App\Services\SecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Mockery;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $twoFactorService;
    protected $securityService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->twoFactorService = Mockery::mock(TwoFactorAuthService::class);
        $this->securityService = Mockery::mock(SecurityService::class);
        
        $this->app->instance(TwoFactorAuthService::class, $this->twoFactorService);
        $this->app->instance(SecurityService::class, $this->securityService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test security page loads correctly
     */
    public function test_security_page_loads_correctly(): void
    {
        $user = User::factory()->create();

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(false);
        $this->twoFactorService->shouldReceive('getRemainingRecoveryCodesCount')->once()->andReturn(0);
        $this->securityService->shouldReceive('getLoginStatistics')->once()->andReturn([]);
        $this->securityService->shouldReceive('getRecentLoginHistory')->once()->andReturn([]);
        $this->securityService->shouldReceive('getActiveSessions')->once()->andReturn([]);
        $this->securityService->shouldReceive('performSecurityCheckup')->once()->andReturn([]);

        $response = $this
            ->actingAs($user)
            ->get('/profile/security');

        $response->assertStatus(200)
                 ->assertViewIs('profile.security')
                 ->assertViewHas(['user', 'twoFactorEnabled', 'loginStatistics']);
    }

    /**
     * Test two-factor authentication status is displayed correctly
     */
    public function test_two_factor_auth_status_display(): void
    {
        $user = User::factory()->create();

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(true);
        $this->twoFactorService->shouldReceive('getRemainingRecoveryCodesCount')->once()->andReturn(8);
        $this->securityService->shouldReceive('getLoginStatistics')->once()->andReturn([]);
        $this->securityService->shouldReceive('getRecentLoginHistory')->once()->andReturn([]);
        $this->securityService->shouldReceive('getActiveSessions')->once()->andReturn([]);
        $this->securityService->shouldReceive('performSecurityCheckup')->once()->andReturn([]);

        $response = $this
            ->actingAs($user)
            ->get('/profile/security');

        $response->assertViewHas('twoFactorEnabled', true)
                 ->assertViewHas('remainingRecoveryCodes', 8);
    }

    /**
     * Test downloading backup codes requires 2FA enabled
     */
    public function test_download_backup_codes_requires_2fa_enabled(): void
    {
        $user = User::factory()->create();

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(false);

        $response = $this
            ->actingAs($user)
            ->get('/profile/download-backup-codes');

        $response->assertRedirect()
                 ->assertSessionHasErrors(['error']);
    }

    /**
     * Test downloading backup codes when 2FA is enabled
     */
    public function test_download_backup_codes_when_2fa_enabled(): void
    {
        $user = User::factory()->create();
        $recoveryCodes = ['code1', 'code2', 'code3'];

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(true);
        $this->twoFactorService->shouldReceive('getRecoveryCodes')->once()->andReturn($recoveryCodes);

        $response = $this
            ->actingAs($user)
            ->get('/profile/download-backup-codes');

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/plain')
                 ->assertHeader('Content-Disposition');

        $content = $response->getContent();
        $this->assertStringContainsString('HD Tickets - Two-Factor Authentication Backup Codes', $content);
        $this->assertStringContainsString('code1', $content);
        $this->assertStringContainsString('code2', $content);
        $this->assertStringContainsString('code3', $content);
    }

    /**
     * Test trusting a device
     */
    public function test_trust_device(): void
    {
        $user = User::factory()->create();

        $this->securityService->shouldReceive('trustDevice')->once();

        $response = $this
            ->actingAs($user)
            ->post('/profile/trust-device');

        $response->assertRedirect()
                 ->assertSessionHas('success', 'Device has been marked as trusted.');
    }

    /**
     * Test removing a trusted device
     */
    public function test_remove_trusted_device(): void
    {
        $user = User::factory()->create();
        $deviceIndex = 0;

        $this->securityService->shouldReceive('untrustDevice')
                             ->once()
                             ->with($user, $deviceIndex)
                             ->andReturn(true);

        $response = $this
            ->actingAs($user)
            ->delete('/profile/trusted-device/' . $deviceIndex);

        $response->assertRedirect()
                 ->assertSessionHas('success', 'Trusted device has been removed.');
    }

    /**
     * Test removing non-existent trusted device
     */
    public function test_remove_nonexistent_trusted_device(): void
    {
        $user = User::factory()->create();
        $deviceIndex = 999;

        $this->securityService->shouldReceive('untrustDevice')
                             ->once()
                             ->with($user, $deviceIndex)
                             ->andReturn(false);

        $response = $this
            ->actingAs($user)
            ->delete('/profile/trusted-device/' . $deviceIndex);

        $response->assertRedirect()
                 ->assertSessionHasErrors(['error']);
    }

    /**
     * Test revoking a session
     */
    public function test_revoke_session(): void
    {
        $user = User::factory()->create();
        $sessionId = 'test-session-id';

        $this->securityService->shouldReceive('revokeSession')
                             ->once()
                             ->with($sessionId)
                             ->andReturn(true);

        $response = $this
            ->actingAs($user)
            ->delete('/profile/session/' . $sessionId);

        $response->assertRedirect()
                 ->assertSessionHas('success', 'Session has been revoked.');
    }

    /**
     * Test revoking non-existent session
     */
    public function test_revoke_nonexistent_session(): void
    {
        $user = User::factory()->create();
        $sessionId = 'nonexistent-session-id';

        $this->securityService->shouldReceive('revokeSession')
                             ->once()
                             ->with($sessionId)
                             ->andReturn(false);

        $response = $this
            ->actingAs($user)
            ->delete('/profile/session/' . $sessionId);

        $response->assertRedirect()
                 ->assertSessionHasErrors(['error']);
    }

    /**
     * Test revoking all other sessions
     */
    public function test_revoke_all_other_sessions(): void
    {
        $user = User::factory()->create();
        $currentSessionId = Session::getId();

        $this->securityService->shouldReceive('revokeAllOtherSessions')
                             ->once()
                             ->with($user, $currentSessionId)
                             ->andReturn(3);

        $response = $this
            ->actingAs($user)
            ->delete('/profile/sessions/revoke-all');

        $response->assertRedirect()
                 ->assertSessionHas('success', 'Revoked 3 other sessions.');
    }

    /**
     * Test revoking all other sessions when none exist
     */
    public function test_revoke_all_other_sessions_when_none_exist(): void
    {
        $user = User::factory()->create();
        $currentSessionId = Session::getId();

        $this->securityService->shouldReceive('revokeAllOtherSessions')
                             ->once()
                             ->with($user, $currentSessionId)
                             ->andReturn(0);

        $response = $this
            ->actingAs($user)
            ->delete('/profile/sessions/revoke-all');

        $response->assertRedirect()
                 ->assertSessionHas('info', 'No other sessions to revoke.');
    }

    /**
     * Test unauthenticated users cannot access security endpoints
     */
    public function test_unauthenticated_users_cannot_access_security_endpoints(): void
    {
        $this->get('/profile/security')->assertRedirect('/login');
        $this->get('/profile/download-backup-codes')->assertRedirect('/login');
        $this->post('/profile/trust-device')->assertRedirect('/login');
        $this->delete('/profile/trusted-device/0')->assertRedirect('/login');
        $this->delete('/profile/session/test')->assertRedirect('/login');
        $this->delete('/profile/sessions/revoke-all')->assertRedirect('/login');
    }

    /**
     * Test password change updates security information
     */
    public function test_password_change_updates_security_info(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password')
        ]);

        $response = $this
            ->actingAs($user)
            ->put('/password', [
                'current_password' => 'old-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

        $response->assertSessionHasNoErrors();

        // Verify password was updated
        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password', $user->password));
    }

    /**
     * Test login history is tracked correctly
     */
    public function test_login_history_tracking(): void
    {
        $user = User::factory()->create();
        
        // Create some login history records
        LoginHistory::factory()->count(3)->create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'login_at' => now()->subHours(2)
        ]);

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(false);
        $this->twoFactorService->shouldReceive('getRemainingRecoveryCodesCount')->once()->andReturn(0);
        $this->securityService->shouldReceive('getLoginStatistics')->once()->andReturn([
            'total_logins' => 3,
            'unique_locations' => 1,
            'last_login' => now()->subHours(2)
        ]);
        $this->securityService->shouldReceive('getRecentLoginHistory')->once()->andReturn([
            ['ip_address' => '192.168.1.1', 'location' => 'Test Location', 'login_at' => now()->subHours(2)]
        ]);
        $this->securityService->shouldReceive('getActiveSessions')->once()->andReturn([]);
        $this->securityService->shouldReceive('performSecurityCheckup')->once()->andReturn([]);

        $response = $this
            ->actingAs($user)
            ->get('/profile/security');

        $response->assertStatus(200)
                 ->assertViewHas('loginStatistics')
                 ->assertViewHas('recentLoginHistory');
    }

    /**
     * Test security checkup warnings are displayed
     */
    public function test_security_checkup_warnings_display(): void
    {
        $user = User::factory()->create();

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(false);
        $this->twoFactorService->shouldReceive('getRemainingRecoveryCodesCount')->once()->andReturn(0);
        $this->securityService->shouldReceive('getLoginStatistics')->once()->andReturn([]);
        $this->securityService->shouldReceive('getRecentLoginHistory')->once()->andReturn([]);
        $this->securityService->shouldReceive('getActiveSessions')->once()->andReturn([]);
        $this->securityService->shouldReceive('performSecurityCheckup')->once()->andReturn([
            'weak_password' => true,
            'no_2fa' => true,
            'old_password' => false
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile/security');

        $response->assertStatus(200)
                 ->assertViewHas('securityCheckup');
    }

    /**
     * Test active sessions are displayed
     */
    public function test_active_sessions_display(): void
    {
        $user = User::factory()->create();

        $activeSessions = [
            [
                'id' => 'session1',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Chrome Browser',
                'last_activity' => now(),
                'is_current' => true
            ],
            [
                'id' => 'session2',
                'ip_address' => '10.0.0.1',
                'user_agent' => 'Firefox Browser',
                'last_activity' => now()->subHour(),
                'is_current' => false
            ]
        ];

        $this->twoFactorService->shouldReceive('isEnabled')->once()->andReturn(false);
        $this->twoFactorService->shouldReceive('getRemainingRecoveryCodesCount')->once()->andReturn(0);
        $this->securityService->shouldReceive('getLoginStatistics')->once()->andReturn([]);
        $this->securityService->shouldReceive('getRecentLoginHistory')->once()->andReturn([]);
        $this->securityService->shouldReceive('getActiveSessions')->once()->andReturn($activeSessions);
        $this->securityService->shouldReceive('performSecurityCheckup')->once()->andReturn([]);

        $response = $this
            ->actingAs($user)
            ->get('/profile/security');

        $response->assertStatus(200)
                 ->assertViewHas('activeSessions', $activeSessions);
    }
}
