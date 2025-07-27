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
        // Purchase tracking for analytics and ML training
        Schema::create('purchase_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->boolean('success');
            $table->string('platform');
            $table->integer('execution_time')->default(0); // milliseconds
            $table->decimal('final_price', 10, 2)->default(0);
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            $table->index(['platform', 'success']);
            $table->index(['user_id', 'created_at']);
        });
        
        // Automation tracking for optimization and insights
        Schema::create('automation_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('platform');
            $table->boolean('success');
            $table->integer('execution_time')->default(0); // milliseconds
            $table->decimal('price_difference', 10, 2)->default(0);
            $table->foreignId('user_id')->constrained();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();
            
            $table->index(['platform', 'success', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
        
        // ML model performance tracking
        Schema::create('ml_model_performance', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('version');
            $table->decimal('accuracy_score', 5, 4)->default(0);
            $table->json('performance_metrics');
            $table->timestamp('evaluated_at');
            $table->json('training_data_stats')->nullable();
            $table->timestamps();
            
            $table->index(['model_name', 'version']);
            $table->index('evaluated_at');
        });
        
        // Parameter adjustment history
        Schema::create('automation_parameter_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('parameter_name');
            $table->json('old_value');
            $table->json('new_value');
            $table->string('adjustment_reason');
            $table->decimal('performance_impact', 5, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['parameter_name', 'created_at']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_parameter_adjustments');
        Schema::dropIfExists('ml_model_performance');
        Schema::dropIfExists('automation_tracking');
        Schema::dropIfExists('purchase_tracking');
    }
};
