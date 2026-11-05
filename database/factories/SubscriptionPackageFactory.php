<?php

namespace Database\Factories;

use App\Enums\BillingCycleEnum;
use App\Models\SubscriptionPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPackage>
 */
class SubscriptionPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => 'Monthly plan', 'ar' => 'الباقة الشهرية'],
            'billing_cycle' => BillingCycleEnum::Monthly,
            'duration_value' => 1,
            'price' => 115,
            'features' => [],
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (): array => ['price' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
