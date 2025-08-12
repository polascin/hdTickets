<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create user_favorite_teams table
        if (! Schema::hasTable('user_favorite_teams')) {
            Schema::create('user_favorite_teams', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('sport_type', 50); // football, basketball, soccer, etc.
                $table->string('team_name');
                $table->string('team_slug')->nullable(); // for URL-friendly lookups
                $table->string('league', 100)->nullable(); // NFL, NBA, Premier League, etc.
                $table->string('team_logo_url')->nullable();
                $table->string('team_city')->nullable();
                $table->json('aliases')->nullable(); // Alternative team names for matching
                $table->boolean('email_alerts')->default(TRUE);
                $table->boolean('push_alerts')->default(TRUE);
                $table->boolean('sms_alerts')->default(FALSE);
                $table->integer('priority')->default(1); // 1-5 priority level
                $table->timestamps();

                $table->unique(['user_id', 'team_slug'], 'user_team_unique');
                $table->index(['user_id', 'sport_type']);
                $table->index(['sport_type', 'league']);
                $table->index(['team_slug']);
            });
        }

        // Create user_favorite_venues table
        if (! Schema::hasTable('user_favorite_venues')) {
            Schema::create('user_favorite_venues', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('venue_name');
                $table->string('venue_slug')->nullable();
                $table->string('city');
                $table->string('state_province')->nullable();
                $table->string('country', 3)->default('USA');
                $table->integer('capacity')->nullable();
                $table->json('venue_types')->nullable(); // stadium, arena, theater, etc.
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('venue_image_url')->nullable();
                $table->json('aliases')->nullable(); // Alternative venue names
                $table->boolean('email_alerts')->default(TRUE);
                $table->boolean('push_alerts')->default(TRUE);
                $table->boolean('sms_alerts')->default(FALSE);
                $table->integer('priority')->default(1); // 1-5 priority level
                $table->timestamps();

                $table->unique(['user_id', 'venue_slug'], 'user_venue_unique');
                $table->index(['user_id', 'city']);
                $table->index(['venue_slug']);
                $table->index(['country', 'state_province']);
            });
        }

        // Create user_price_preferences table
        if (! Schema::hasTable('user_price_preferences')) {
            Schema::create('user_price_preferences', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('preference_name'); // Custom name for this preference set
                $table->string('sport_type')->nullable();
                $table->string('event_category')->nullable(); // regular season, playoffs, championship
                $table->decimal('min_price', 10, 2)->nullable();
                $table->decimal('max_price', 10, 2);
                $table->integer('preferred_quantity')->default(2);
                $table->json('seat_preferences')->nullable(); // lower, upper, club, suite, etc.
                $table->json('section_preferences')->nullable(); // specific sections
                $table->decimal('price_drop_threshold', 5, 2)->default(15.00); // Alert on % drop
                $table->decimal('price_increase_threshold', 5, 2)->default(25.00); // Alert on % increase
                $table->boolean('auto_purchase_enabled')->default(FALSE);
                $table->decimal('auto_purchase_max_price', 10, 2)->nullable();
                $table->boolean('email_alerts')->default(TRUE);
                $table->boolean('push_alerts')->default(TRUE);
                $table->boolean('sms_alerts')->default(FALSE);
                $table->enum('alert_frequency', ['immediate', 'hourly', 'daily'])->default('immediate');
                $table->boolean('is_active')->default(TRUE);
                $table->timestamps();

                $table->index(['user_id', 'sport_type']);
                $table->index(['user_id', 'is_active']);
                $table->index(['min_price', 'max_price']);
            });
        }

        // Create user_sports_preferences table for general sports settings
        if (! Schema::hasTable('user_sports_preferences')) {
            Schema::create('user_sports_preferences', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->json('favorite_sports')->nullable(); // Array of preferred sports
                $table->json('excluded_sports')->nullable(); // Sports to ignore
                $table->boolean('include_playoffs')->default(TRUE);
                $table->boolean('include_preseason')->default(FALSE);
                $table->boolean('include_exhibitions')->default(FALSE);
                $table->boolean('weekend_preference')->default(FALSE);
                $table->json('preferred_times')->nullable(); // Time ranges for events
                $table->integer('max_travel_distance')->nullable(); // Miles willing to travel
                $table->string('home_location')->nullable(); // City for distance calculations
                $table->decimal('default_max_budget', 10, 2)->default(500.00);
                $table->enum('ticket_delivery_preference', ['electronic', 'mobile', 'physical'])->default('mobile');
                $table->boolean('parking_alerts')->default(FALSE);
                $table->boolean('weather_alerts')->default(FALSE);
                $table->timestamps();

                $table->unique(['user_id']);
            });
        }

        // Create user_seat_preferences table for detailed seating preferences
        if (! Schema::hasTable('user_seat_preferences')) {
            Schema::create('user_seat_preferences', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('venue_type'); // stadium, arena, theater, etc.
                $table->json('preferred_levels')->nullable(); // lower, club, upper
                $table->json('preferred_locations')->nullable(); // behind plate, sideline, etc.
                $table->json('accessibility_needs')->nullable(); // wheelchair, aisle, etc.
                $table->boolean('covered_seating')->default(FALSE);
                $table->boolean('close_to_amenities')->default(FALSE);
                $table->integer('max_row')->nullable();
                $table->integer('min_row')->nullable();
                $table->boolean('aisle_seats_preferred')->default(FALSE);
                $table->boolean('avoid_sun_glare')->default(FALSE);
                $table->integer('group_size_preference')->default(2);
                $table->timestamps();

                $table->unique(['user_id', 'venue_type'], 'user_venue_type_unique');
            });
        }

        // Add indexes for better query performance (only for MySQL)
        if (DB::getDriverName() === 'mysql') {
            Schema::table('user_favorite_teams', function (Blueprint $table): void {
                $table->fullText(['team_name', 'team_city'], 'teams_search_fulltext');
            });

            Schema::table('user_favorite_venues', function (Blueprint $table): void {
                $table->fullText(['venue_name', 'city'], 'venues_search_fulltext');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_seat_preferences');
        Schema::dropIfExists('user_sports_preferences');
        Schema::dropIfExists('user_price_preferences');
        Schema::dropIfExists('user_favorite_venues');
        Schema::dropIfExists('user_favorite_teams');
    }
};
