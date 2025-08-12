<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations - Phase 4 Schema Normalization
     */
    public function up(): void
    {
        // 1. Create normalized tables for JSON data
        $this->createNormalizedTables();

        // 2. Create junction tables for many-to-many relationships
        $this->createJunctionTables();

        // 3. Add proper foreign key constraints
        $this->addForeignKeyConstraints();

        // 4. Optimize data types and remove redundancies
        $this->optimizeDataTypes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop junction tables
        Schema::dropIfExists('alert_event_criteria');
        Schema::dropIfExists('user_platform_preferences');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('user_sport_preferences');

        // Drop normalized tables
        Schema::dropIfExists('scraping_selector_metrics');
        Schema::dropIfExists('platform_config_details');
        Schema::dropIfExists('ticket_seat_details');
        Schema::dropIfExists('event_metadata');
        Schema::dropIfExists('user_preference_categories');
    }

    /**
     * Create normalized tables for JSON data
     */
    private function createNormalizedTables(): void
    {
        // User preferences normalized
        if (! Schema::hasTable('user_preference_categories')) {
            Schema::create('user_preference_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(TRUE);
                $table->timestamps();

                $table->index('name');
                $table->index('is_active');
            });
        }

        // Sports event metadata normalized
        if (! Schema::hasTable('event_metadata')) {
            Schema::create('event_metadata', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->string('key', 100);
                $table->text('value');
                $table->enum('type', ['string', 'integer', 'decimal', 'boolean', 'json'])->default('string');
                $table->timestamps();

                $table->unique(['event_id', 'key']);
                $table->index(['event_id', 'key']);
                $table->index('key');
            });
        }

        // Ticket seat details normalized
        if (! Schema::hasTable('ticket_seat_details')) {
            Schema::create('ticket_seat_details', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->string('section', 100)->nullable();
                $table->string('row', 20)->nullable();
                $table->string('seat_number', 20)->nullable();
                $table->string('seat_type', 50)->nullable(); // VIP, Standard, etc.
                $table->decimal('seat_price', 10, 2)->nullable();
                $table->json('accessibility_features')->nullable();
                $table->timestamps();

                $table->index('ticket_id');
                $table->index(['section', 'row']);
                $table->index('seat_type');
            });
        }

        // Scraping selectors effectiveness normalized
        if (! Schema::hasTable('scraping_selector_metrics')) {
            Schema::create('scraping_selector_metrics', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('selector_id');
                $table->string('metric_name', 100);
                $table->decimal('metric_value', 10, 4);
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->foreign('selector_id')->references('id')->on('selector_effectiveness')->onDelete('cascade');
                $table->index(['selector_id', 'recorded_at']);
                $table->index(['metric_name', 'recorded_at']);
            });
        }

        // Platform configuration details normalized
        if (! Schema::hasTable('platform_config_details')) {
            Schema::create('platform_config_details', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('config_id');
                $table->string('detail_key', 100);
                $table->text('detail_value');
                $table->enum('value_type', ['string', 'integer', 'decimal', 'boolean', 'array'])->default('string');
                $table->boolean('is_encrypted')->default(FALSE);
                $table->timestamps();

                $table->foreign('config_id')->references('id')->on('platform_configurations')->onDelete('cascade');
                $table->unique(['config_id', 'detail_key']);
                $table->index(['config_id', 'detail_key']);
            });
        }
    }

    /**
     * Create junction tables for many-to-many relationships
     */
    private function createJunctionTables(): void
    {
        // User-Sport preferences
        if (! Schema::hasTable('user_sport_preferences')) {
            Schema::create('user_sport_preferences', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('sport', 100);
                $table->tinyInteger('priority')->default(1); // 1 = highest
                $table->decimal('max_budget', 10, 2)->nullable();
                $table->json('preferred_teams')->nullable();
                $table->json('preferred_venues')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id', 'sport']);
                $table->index(['user_id', 'priority']);
                $table->index('sport');
            });
        }

        // Event-Category relationships (many-to-many)
        if (! Schema::hasTable('event_categories')) {
            Schema::create('event_categories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('category_id');
                $table->tinyInteger('relevance_score')->default(100); // 1-100
                $table->timestamps();

                $table->foreign('event_id')->references('id')->on('sports_events')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->unique(['event_id', 'category_id']);
                $table->index(['event_id', 'relevance_score']);
                $table->index(['category_id', 'relevance_score']);
            });
        }

        // User-Platform preferences
        if (! Schema::hasTable('user_platform_preferences')) {
            Schema::create('user_platform_preferences', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('platform', 100);
                $table->tinyInteger('priority')->default(5); // 1-10 scale
                $table->boolean('auto_purchase_enabled')->default(FALSE);
                $table->decimal('max_price_threshold', 10, 2)->nullable();
                $table->json('notification_settings')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id', 'platform']);
                $table->index(['user_id', 'priority']);
                $table->index('platform');
            });
        }

        // Ticket alert criteria (many-to-many with sports events)
        if (! Schema::hasTable('alert_event_criteria')) {
            Schema::create('alert_event_criteria', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('alert_id');
                $table->unsignedBigInteger('event_id')->nullable();
                $table->string('criteria_type', 50); // 'venue', 'team', 'date_range', etc.
                $table->text('criteria_value');
                $table->tinyInteger('weight')->default(1); // Importance weighting
                $table->timestamps();

                $table->foreign('alert_id')->references('id')->on('ticket_alerts')->onDelete('cascade');
                $table->foreign('event_id')->references('id')->on('sports_events')->onDelete('set null');
                $table->index(['alert_id', 'criteria_type']);
                $table->index(['event_id', 'criteria_type']);
            });
        }
    }

    /**
     * Add proper foreign key constraints
     */
    private function addForeignKeyConstraints(): void
    {
        // Add missing foreign key constraints
        Schema::table('event_metadata', function (Blueprint $table): void {
            if (! $this->foreignKeyExists('event_metadata', 'event_metadata_event_id_foreign')) {
                $table->foreign('event_id')->references('id')->on('sports_events')->onDelete('cascade');
            }
        });

        Schema::table('ticket_seat_details', function (Blueprint $table): void {
            if (! $this->foreignKeyExists('ticket_seat_details', 'ticket_seat_details_ticket_id_foreign')) {
                $table->foreign('ticket_id')->references('id')->on('scraped_tickets')->onDelete('cascade');
            }
        });

        // Add constraint for analytics dashboards user relationship
        if (Schema::hasTable('analytics_dashboards')) {
            Schema::table('analytics_dashboards', function (Blueprint $table): void {
                if (! $this->foreignKeyExists('analytics_dashboards', 'analytics_dashboards_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Optimize data types and remove redundancies
     */
    private function optimizeDataTypes(): void
    {
        // Optimize scraped_tickets table
        Schema::table('scraped_tickets', function (Blueprint $table): void {
            // Change text fields to appropriate varchar lengths
            $table->string('external_id', 100)->nullable()->change();
            $table->string('venue', 200)->nullable()->change();
            $table->string('location', 200)->nullable()->change();
            $table->string('team', 200)->nullable()->change();

            // Optimize enum fields
            $table->enum('currency', ['USD', 'EUR', 'GBP', 'CAD', 'AUD'])->default('USD')->change();
        });

        // Optimize users table
        Schema::table('users', function (Blueprint $table): void {
            // Standardize string lengths
            $table->string('phone', 20)->nullable()->change();
            $table->string('timezone', 50)->default('UTC')->change();
            $table->string('language', 10)->default('en')->change();
        });

        // Optimize sports_teams table
        if (Schema::hasTable('sports_teams')) {
            Schema::table('sports_teams', function (Blueprint $table): void {
                $table->string('city', 100)->nullable()->change();
                $table->string('country', 100)->default('United States')->change();
                $table->string('league', 100)->nullable()->change();
            });
        }

        // Optimize sports_venues table
        if (Schema::hasTable('sports_venues')) {
            Schema::table('sports_venues', function (Blueprint $table): void {
                $table->string('city', 100)->change();
                $table->string('state', 100)->nullable()->change();
                $table->string('country', 100)->default('United States')->change();
                $table->string('timezone', 50)->default('America/New_York')->change();
            });
        }
    }

    /**
     * Check if foreign key constraint exists
     */
    private function foreignKeyExists(string $table, string $keyName): bool
    {
        if (DB::getDriverName() === 'mysql') {
            $result = DB::select(
                'SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE 
                WHERE CONSTRAINT_NAME = ? AND TABLE_NAME = ? AND TABLE_SCHEMA = ?',
                [$keyName, $table, config('database.connections.mysql.database')],
            );

            return $result[0]->count > 0;
        }

        return FALSE;
    }
};
