<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sports Teams Table
        Schema::create('sports_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sport');
            $table->string('league')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('United States');
            $table->json('aliases')->nullable(); // Alternative names
            $table->string('logo_url')->nullable();
            $table->json('colors')->nullable(); // Team colors
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['sport', 'is_active']);
            $table->index('league');
        });

        // Sports Venues Table
        Schema::create('sports_venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('United States');
            $table->integer('capacity')->nullable();
            $table->json('coordinates')->nullable(); // lat/lng
            $table->json('aliases')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['city', 'is_active']);
        });

        // Sports Leagues/Competitions Table
        Schema::create('sports_leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sport');
            $table->string('country')->default('United States');
            $table->string('level')->default('professional'); // professional, college, amateur
            $table->json('season_structure')->nullable(); // Regular season, playoffs, etc.
            $table->json('aliases')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['sport', 'is_active']);
        });

        // Scraping Patterns Table for sport-specific keywords
        Schema::create('scraping_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('sport')->nullable();
            $table->string('pattern_type'); // team_name, venue, event_type, etc.
            $table->text('pattern'); // Regex or keyword pattern
            $table->json('replacement')->nullable(); // Standard replacement
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(10);
            $table->timestamps();
            
            $table->index(['platform', 'sport', 'is_active']);
            $table->index('pattern_type');
        });

        // Platform Configuration Table
        Schema::create('platform_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('config_key');
            $table->json('config_value');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['platform', 'config_key']);
            $table->index('platform');
        });

        // Enhanced scraped_tickets with sports relationships
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->foreignId('home_team_id')->nullable()->constrained('sports_teams')->onDelete('set null');
            $table->foreignId('away_team_id')->nullable()->constrained('sports_teams')->onDelete('set null');
            $table->foreignId('venue_id')->nullable()->constrained('sports_venues')->onDelete('set null');
            $table->foreignId('league_id')->nullable()->constrained('sports_leagues')->onDelete('set null');
            $table->string('competition_round')->nullable(); // Regular season, playoffs, finals, etc.
            $table->json('weather_conditions')->nullable();
            $table->decimal('predicted_demand', 5, 2)->nullable(); // 0-100 scale
            
            $table->index(['home_team_id', 'away_team_id']);
            $table->index(['venue_id', 'event_date']);
            $table->index('predicted_demand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints first
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->dropForeign(['home_team_id']);
            $table->dropForeign(['away_team_id']);
            $table->dropForeign(['venue_id']);
            $table->dropForeign(['league_id']);
            $table->dropColumn([
                'home_team_id', 'away_team_id', 'venue_id', 'league_id',
                'competition_round', 'weather_conditions', 'predicted_demand'
            ]);
        });
        
        // Drop the new tables
        Schema::dropIfExists('platform_configurations');
        Schema::dropIfExists('scraping_patterns');
        Schema::dropIfExists('sports_leagues');
        Schema::dropIfExists('sports_venues');
        Schema::dropIfExists('sports_teams');
    }
};
