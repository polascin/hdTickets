<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\PurchaseAttempt;
use App\Models\TicketAlert;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_user_with_basic_attributes(): void
    {
        $userData = [
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
            'status'   => 'active',
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('user', $user->role);
        $this->assertEquals('active', $user->status);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    #[Test]
    public function it_has_proper_fillable_attributes(): void
    {
        $user = new User();

        $expectedFillable = [
            'name', 'email', 'password', 'role', 'status',
            'preferences', 'two_factor_secret', 'phone',
            'timezone', 'last_login_at', 'email_verified_at',
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $user->getFillable());
        }
    }

    #[Test]
    public function it_hides_sensitive_attributes(): void
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $hiddenAttributes = ['password', 'remember_token', 'two_factor_secret'];

        foreach ($hiddenAttributes as $attribute) {
            $this->assertArrayNotHasKey($attribute, $userArray);
        }
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $user = User::factory()->create([
            'preferences'       => ['theme' => 'dark', 'notifications' => TRUE],
            'email_verified_at' => now(),
            'last_login_at'     => now(),
        ]);

        $this->assertIsArray($user->preferences);
        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
        $this->assertInstanceOf(Carbon::class, $user->last_login_at);
    }

    #[Test]
    public function it_has_relationship_with_subscriptions(): void
    {
        $user = $this->createTestUser();

        UserSubscription::create([
            'user_id'   => $user->id,
            'plan_name' => 'premium',
            'status'    => 'active',
            'starts_at' => now(),
            'ends_at'   => now()->addYear(),
        ]);

        $this->assertInstanceOf(Collection::class, $user->subscriptions);
        $this->assertEquals(1, $user->subscriptions->count());
        $this->assertEquals('premium', $user->subscriptions->first()->plan_name);
    }

    #[Test]
    public function it_has_relationship_with_ticket_alerts(): void
    {
        $user = $this->createTestUser();

        TicketAlert::create([
            'user_id'               => $user->id,
            'title'                 => 'Football Alerts',
            'criteria'              => ['sport_type' => 'football'],
            'notification_channels' => ['email'],
            'is_active'             => TRUE,
        ]);

        $this->assertInstanceOf(Collection::class, $user->ticketAlerts);
        $this->assertEquals(1, $user->ticketAlerts->count());
        $this->assertEquals('Football Alerts', $user->ticketAlerts->first()->title);
    }

    #[Test]
    public function it_has_relationship_with_purchase_attempts(): void
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        PurchaseAttempt::create([
            'user_id'   => $user->id,
            'ticket_id' => $ticket->id,
            'quantity'  => 2,
            'max_price' => 150.00,
            'status'    => 'pending',
        ]);

        $this->assertInstanceOf(Collection::class, $user->purchaseAttempts);
        $this->assertEquals(1, $user->purchaseAttempts->count());
        $this->assertEquals('pending', $user->purchaseAttempts->first()->status);
    }

    #[Test]
    public function it_can_check_if_user_is_premium(): void
    {
        $user = $this->createTestUser();

        // User without subscription should not be premium
        $this->assertFalse($user->isPremium());

        // Create active subscription
        UserSubscription::create([
            'user_id'   => $user->id,
            'plan_name' => 'premium',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addYear(),
        ]);

        // Refresh user model to load relationships
        $user->refresh();

        $this->assertTrue($user->isPremium());
    }

    #[Test]
    public function it_can_check_if_user_is_admin(): void
    {
        $regularUser = $this->createTestUser(['role' => 'user']);
        $adminUser = $this->createTestUser(['role' => 'admin']);

        $this->assertFalse($regularUser->isAdmin());
        $this->assertTrue($adminUser->isAdmin());
    }

    #[Test]
    public function it_can_get_user_subscription_status(): void
    {
        $user = $this->createTestUser();

        // No subscription
        $this->assertEquals('none', $user->getSubscriptionStatus());

        // Active subscription
        UserSubscription::create([
            'user_id'   => $user->id,
            'plan_name' => 'premium',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addYear(),
        ]);

        $user->refresh();
        $this->assertEquals('active', $user->getSubscriptionStatus());

        // Expired subscription
        $user->subscriptions()->update(['ends_at' => now()->subDay()]);
        $user->refresh();
        $this->assertEquals('expired', $user->getSubscriptionStatus());
    }

    #[Test]
    public function it_can_get_user_preferences_with_defaults(): void
    {
        $user = $this->createTestUser();

        // Test default preferences
        $preferences = $user->getPreferences();

        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('notifications', $preferences);
        $this->assertArrayHasKey('currency', $preferences);
        $this->assertArrayHasKey('timezone', $preferences);
        $this->assertEquals('USD', $preferences['currency']);
        $this->assertEquals('UTC', $preferences['timezone']);
    }

    #[Test]
    public function it_can_update_user_preferences(): void
    {
        $user = $this->createTestUser();

        $newPreferences = [
            'currency' => 'EUR',
            'timezone' => 'Europe/London',
            'theme'    => 'dark',
        ];

        $user->updatePreferences($newPreferences);

        $preferences = $user->getPreferences();
        $this->assertEquals('EUR', $preferences['currency']);
        $this->assertEquals('Europe/London', $preferences['timezone']);
        $this->assertEquals('dark', $preferences['theme']);
    }

    #[Test]
    public function it_can_enable_two_factor_authentication(): void
    {
        $user = $this->createTestUser();

        $secret = 'test_secret_key';
        $user->enableTwoFactorAuth($secret);

        $this->assertNotNull($user->two_factor_secret);
        $this->assertEquals($secret, $user->two_factor_secret);
    }

    #[Test]
    public function it_can_disable_two_factor_authentication(): void
    {
        $user = $this->createTestUser(['two_factor_secret' => 'test_secret']);

        $user->disableTwoFactorAuth();

        $this->assertNull($user->two_factor_secret);
    }

    #[Test]
    public function it_can_check_if_two_factor_is_enabled(): void
    {
        $user = $this->createTestUser();

        $this->assertFalse($user->hasTwoFactorEnabled());

        $user->update(['two_factor_secret' => 'test_secret']);

        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    #[Test]
    public function it_can_get_active_ticket_alerts_count(): void
    {
        $user = $this->createTestUser();

        // Create some alerts
        TicketAlert::create([
            'user_id'               => $user->id,
            'title'                 => 'Active Alert 1',
            'criteria'              => ['sport_type' => 'football'],
            'notification_channels' => ['email'],
            'is_active'             => TRUE,
        ]);

        TicketAlert::create([
            'user_id'               => $user->id,
            'title'                 => 'Active Alert 2',
            'criteria'              => ['sport_type' => 'basketball'],
            'notification_channels' => ['email'],
            'is_active'             => TRUE,
        ]);

        TicketAlert::create([
            'user_id'               => $user->id,
            'title'                 => 'Inactive Alert',
            'criteria'              => ['sport_type' => 'baseball'],
            'notification_channels' => ['email'],
            'is_active'             => FALSE,
        ]);

        $this->assertEquals(2, $user->getActiveAlertsCount());
    }

    #[Test]
    public function it_can_get_recent_purchase_attempts(): void
    {
        $user = $this->createTestUser();
        $ticket = $this->createTestTicket();

        // Create purchase attempts at different times
        PurchaseAttempt::create([
            'user_id'    => $user->id,
            'ticket_id'  => $ticket->id,
            'quantity'   => 1,
            'max_price'  => 100,
            'status'     => 'completed',
            'created_at' => now()->subDays(5),
        ]);

        PurchaseAttempt::create([
            'user_id'    => $user->id,
            'ticket_id'  => $ticket->id,
            'quantity'   => 2,
            'max_price'  => 200,
            'status'     => 'pending',
            'created_at' => now()->subHours(2),
        ]);

        $recentAttempts = $user->getRecentPurchaseAttempts(7); // Last 7 days

        $this->assertEquals(2, $recentAttempts->count());
        $this->assertEquals('pending', $recentAttempts->first()->status);
    }

    #[Test]
    public function it_can_soft_delete_user(): void
    {
        $user = $this->createTestUser();

        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertNotNull($user->deleted_at);
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        $this->expectException(QueryException::class);

        User::create([
            'name'     => 'Test User',
            'email'    => 'invalid-email',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);
    }

    #[Test]
    public function it_enforces_unique_email_constraint(): void
    {
        $this->createTestUser(['email' => 'test@example.com']);

        $this->expectException(QueryException::class);

        $this->createTestUser(['email' => 'test@example.com']);
    }

    #[Test]
    public function it_can_scope_users_by_role(): void
    {
        $this->createTestUser(['role' => 'user']);
        $this->createTestUser(['role' => 'admin']);
        $this->createTestUser(['role' => 'premium']);

        $users = User::whereRole('user')->get();
        $admins = User::whereRole('admin')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals(1, $admins->count());
    }

    #[Test]
    public function it_can_scope_active_users(): void
    {
        $this->createTestUser(['status' => 'active']);
        $this->createTestUser(['status' => 'inactive']);
        $this->createTestUser(['status' => 'suspended']);

        $activeUsers = User::active()->get();

        $this->assertEquals(1, $activeUsers->count());
        $this->assertEquals('active', $activeUsers->first()->status);
    }
}
