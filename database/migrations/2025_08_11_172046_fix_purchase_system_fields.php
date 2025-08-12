<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix purchase_queues table if it exists
        if (Schema::hasTable('purchase_queues')) {
            Schema::table('purchase_queues', function (Blueprint $table): void {
                if (! Schema::hasColumn('purchase_queues', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('selected_by_user_id')->constrained()->onDelete('cascade');
                }
                if (! Schema::hasColumn('purchase_queues', 'transaction_id')) {
                    $table->string('transaction_id')->nullable()->after('priority');
                }
                if (! Schema::hasColumn('purchase_queues', 'metadata')) {
                    $table->json('metadata')->nullable()->after('notes');
                }
            });
        }

        // Fix purchase_attempts table to ensure all needed fields exist if it exists
        if (Schema::hasTable('purchase_attempts')) {
            Schema::table('purchase_attempts', function (Blueprint $table): void {
                if (! Schema::hasColumn('purchase_attempts', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('uuid')->constrained()->onDelete('cascade');
                }
                if (! Schema::hasColumn('purchase_attempts', 'platform')) {
                    $table->string('platform')->nullable()->after('scraped_ticket_id');
                }
                if (! Schema::hasColumn('purchase_attempts', 'transaction_id')) {
                    $table->string('transaction_id')->nullable()->after('status');
                }
                if (! Schema::hasColumn('purchase_attempts', 'total_paid')) {
                    $table->decimal('total_paid', 10, 2)->nullable()->after('final_price');
                }
                if (! Schema::hasColumn('purchase_attempts', 'platform_fee')) {
                    $table->decimal('platform_fee', 10, 2)->nullable()->after('fees');
                }
                if (! Schema::hasColumn('purchase_attempts', 'metadata')) {
                    $table->json('metadata')->nullable()->after('response_data');
                }
            });
        }

        // Create purchase tracking table for analytics
        if (! Schema::hasTable('purchase_tracking')) {
            Schema::create('purchase_tracking', function (Blueprint $table): void {
                $table->id();
                $table->string('transaction_id')->nullable();
                $table->boolean('success')->default(FALSE);
                $table->string('platform')->nullable();
                $table->integer('execution_time')->default(0); // milliseconds
                $table->decimal('final_price', 10, 2)->nullable();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->index(['success', 'platform', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        // Create automation tracking table
        if (! Schema::hasTable('automation_tracking')) {
            Schema::create('automation_tracking', function (Blueprint $table): void {
                $table->id();
                $table->string('transaction_id');
                $table->string('platform');
                $table->boolean('success')->default(FALSE);
                $table->integer('execution_time')->default(0);
                $table->decimal('price_difference', 10, 2)->default(0);
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->index(['platform', 'success', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_queues')) {
            Schema::table('purchase_queues', function (Blueprint $table): void {
                $table->dropColumn(['user_id', 'transaction_id', 'metadata']);
            });
        }

        if (Schema::hasTable('purchase_attempts')) {
            Schema::table('purchase_attempts', function (Blueprint $table): void {
                $table->dropColumn(['user_id', 'platform', 'transaction_id', 'total_paid', 'platform_fee', 'metadata']);
            });
        }

        Schema::dropIfExists('purchase_tracking');
        Schema::dropIfExists('automation_tracking');
    }
};
