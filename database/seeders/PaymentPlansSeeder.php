<?php declare(strict_types=1);

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
        // Idempotent seeding: upsert by unique slug, do not truncate to avoid FK issues
        $defaults = PaymentPlan::getDefaultPlans();

        foreach ($defaults as $planData) {
            if (! isset($planData['slug'])) {
                // Skip invalid entries without slug
                continue;
            }

            PaymentPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData,
            );
        }

        // Optionally, deactivate plans not present in defaults instead of deleting
        // PaymentPlan::whereNotIn('slug', collect($defaults)->pluck('slug'))->update(['is_active' => false]);

        $this->command?->info('Payment plans seeded successfully (idempotent)!');
    }
}
