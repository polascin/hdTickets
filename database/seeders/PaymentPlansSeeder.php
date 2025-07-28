<?php

namespace Database\Seeders;

use App\Models\PaymentPlan;
use Illuminate\Database\Seeder;

class PaymentPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing plans
        PaymentPlan::truncate();

        // Create default payment plans
        foreach (PaymentPlan::getDefaultPlans() as $planData) {
            PaymentPlan::create($planData);
        }

        $this->command->info('Payment plans seeded successfully!');
    }
}
