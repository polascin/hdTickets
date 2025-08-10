<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFavoriteTeam;
use App\Models\UserFavoriteVenue;
use App\Models\UserPricePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferencesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test preferences page loads correctly
     */
    public function test_preferences_page_loads_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/preferences');

        $response->assertStatus(200)
                 ->assertViewIs('profile.preferences')
                 ->assertViewHas('user');
    }

    /**
     * Test user can update single preference
     */
    public function test_user_can_update_single_preference(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/update-single', [
                'key' => 'theme',
                'value' => 'dark',
                'type' => 'string'
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /**
     * Test user can update multiple preferences
     */
    public function test_user_can_update_multiple_preferences(): void
    {
        $user = User::factory()->create();

        $preferences = [
            'notifications' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true
            ],
            'display' => [
                'theme' => 'dark',
                'sidebar_collapsed' => false,
                'animation_enabled' => true
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/update', [
                'preferences' => $preferences
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /**
     * Test user can detect and update timezone
     */
    public function test_user_can_detect_timezone(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/detect-timezone', [
                'timezone' => 'America/New_York'
            ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'timezone',
                     'display_name'
                 ]);
    }

    /**
     * Test user can export preferences
     */
    public function test_user_can_export_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/preferences/export');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data'
                 ]);
    }

    /**
     * Test user can import preferences
     */
    public function test_user_can_import_preferences(): void
    {
        $user = User::factory()->create();

        $preferences = [
            'theme' => 'dark',
            'email_notifications' => false,
            'dashboard_refresh_interval' => 30
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/import', [
                'preferences' => $preferences,
                'overwrite' => true
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /**
     * Test user can reset preferences
     */
    public function test_user_can_reset_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/reset', [
                'categories' => ['display', 'notifications']
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure([
                     'success',
                     'preferences'
                 ]);
    }

    /**
     * Test user can add favorite team
     */
    public function test_user_can_add_favorite_team(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/user/favorite-teams', [
                'team_name' => 'Manchester United',
                'sport' => 'Football',
                'league' => 'Premier League',
                'country' => 'England',
                'priority' => 1,
                'alert_enabled' => true
            ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'team_name',
                         'sport',
                         'league'
                     ]
                 ]);

        $this->assertDatabaseHas('user_favorite_teams', [
            'user_id' => $user->id,
            'team_name' => 'Manchester United',
            'sport' => 'Football'
        ]);
    }

    /**
     * Test user can update favorite team
     */
    public function test_user_can_update_favorite_team(): void
    {
        $user = User::factory()->create();
        $team = UserFavoriteTeam::factory()->create([
            'user_id' => $user->id,
            'team_name' => 'Arsenal FC',
            'priority' => 2
        ]);

        $response = $this
            ->actingAs($user)
            ->putJson("/user/favorite-teams/{$team->id}", [
                'team_name' => 'Arsenal FC',
                'sport' => 'Football',
                'league' => 'Premier League',
                'country' => 'England',
                'priority' => 1,
                'alert_enabled' => true
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_favorite_teams', [
            'id' => $team->id,
            'priority' => 1
        ]);
    }

    /**
     * Test user can delete favorite team
     */
    public function test_user_can_delete_favorite_team(): void
    {
        $user = User::factory()->create();
        $team = UserFavoriteTeam::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson("/user/favorite-teams/{$team->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('user_favorite_teams', [
            'id' => $team->id
        ]);
    }

    /**
     * Test user can add favorite venue
     */
    public function test_user_can_add_favorite_venue(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/user/favorite-venues', [
                'venue_name' => 'Wembley Stadium',
                'city' => 'London',
                'country' => 'England',
                'venue_type' => 'Stadium',
                'priority' => 1,
                'alert_enabled' => true
            ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'venue_name',
                         'city',
                         'country'
                     ]
                 ]);

        $this->assertDatabaseHas('user_favorite_venues', [
            'user_id' => $user->id,
            'venue_name' => 'Wembley Stadium',
            'city' => 'London'
        ]);
    }

    /**
     * Test user can set price preferences
     */
    public function test_user_can_set_price_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/user/price-preferences', [
                'category' => 'Football',
                'min_price' => 50,
                'max_price' => 200,
                'currency' => 'GBP',
                'alert_threshold_percentage' => 10,
                'is_active' => true
            ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_price_preferences', [
            'user_id' => $user->id,
            'category' => 'Football',
            'min_price' => 50,
            'max_price' => 200
        ]);
    }

    /**
     * Test user cannot access other users' preferences
     */
    public function test_user_cannot_access_other_users_preferences(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $team = UserFavoriteTeam::factory()->create([
            'user_id' => $user2->id
        ]);

        $response = $this
            ->actingAs($user1)
            ->deleteJson("/user/favorite-teams/{$team->id}");

        $response->assertStatus(404);
    }

    /**
     * Test preferences validation
     */
    public function test_preferences_validation(): void
    {
        $user = User::factory()->create();

        // Test invalid preference key
        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/update-single', [
                'key' => '', // Empty key should fail
                'value' => 'dark',
                'type' => 'string'
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['key']);

        // Test invalid team data
        $response = $this
            ->actingAs($user)
            ->postJson('/user/favorite-teams', [
                'team_name' => '', // Empty name should fail
                'sport' => 'Football'
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['team_name']);

        // Test invalid price preferences
        $response = $this
            ->actingAs($user)
            ->postJson('/user/price-preferences', [
                'category' => 'Football',
                'min_price' => 300, // Min price higher than max price
                'max_price' => 200,
                'currency' => 'GBP'
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['min_price']);
    }

    /**
     * Test unauthenticated users cannot access preferences
     */
    public function test_unauthenticated_users_cannot_access_preferences(): void
    {
        $this->getJson('/preferences')->assertStatus(401);
        $this->postJson('/preferences/update-single')->assertStatus(401);
        $this->postJson('/user/favorite-teams')->assertStatus(401);
        $this->postJson('/user/favorite-venues')->assertStatus(401);
        $this->postJson('/user/price-preferences')->assertStatus(401);
    }

    /**
     * Test preferences are properly categorized
     */
    public function test_preferences_categorization(): void
    {
        $user = User::factory()->create();

        $preferences = [
            'notifications' => [
                'email_notifications' => true,
                'push_notifications' => false,
                'quiet_hours_start' => '22:00',
                'quiet_hours_end' => '07:00'
            ],
            'display' => [
                'theme' => 'dark',
                'sidebar_collapsed' => true,
                'animation_enabled' => false,
                'display_density' => 'compact'
            ],
            'alerts' => [
                'price_alert_threshold' => 15,
                'alert_escalation_enabled' => true,
                'alert_frequency' => 'immediate'
            ],
            'performance' => [
                'dashboard_refresh_interval' => 30,
                'lazy_loading_enabled' => true,
                'compression_enabled' => true
            ]
        ];

        $response = $this
            ->actingAs($user)
            ->postJson('/preferences/update', [
                'preferences' => $preferences
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /**
     * Test bulk operations on favorite teams
     */
    public function test_bulk_operations_on_favorite_teams(): void
    {
        $user = User::factory()->create();
        
        // Create multiple favorite teams
        $teams = UserFavoriteTeam::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        // Test bulk update priorities
        $response = $this
            ->actingAs($user)
            ->putJson('/user/favorite-teams/bulk-update', [
                'updates' => [
                    ['id' => $teams[0]->id, 'priority' => 1],
                    ['id' => $teams[1]->id, 'priority' => 2],
                    ['id' => $teams[2]->id, 'priority' => 3]
                ]
            ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify priorities were updated
        $this->assertDatabaseHas('user_favorite_teams', [
            'id' => $teams[0]->id,
            'priority' => 1
        ]);
    }

    /**
     * Test preferences export includes all user data
     */
    public function test_preferences_export_includes_all_data(): void
    {
        $user = User::factory()->create();
        
        // Create some user data
        UserFavoriteTeam::factory()->create(['user_id' => $user->id]);
        UserFavoriteVenue::factory()->create(['user_id' => $user->id]);
        UserPricePreference::factory()->create(['user_id' => $user->id]);

        $response = $this
            ->actingAs($user)
            ->getJson('/preferences/export');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'user_preferences',
                         'favorite_teams',
                         'favorite_venues',
                         'price_preferences'
                     ]
                 ]);
    }
}
