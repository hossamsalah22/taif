<?php

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => User::factory(),
            'subscription_package_id' => SubscriptionPackage::factory(),
            'gateway' => 'moyasar',
            'gateway_invoice_id' => (string) Str::uuid(),
            'amount' => 11500,
            'currency' => 'SAR',
            'status' => PaymentStatusEnum::PENDING,
            'payment_url' => 'https://checkout.moyasar.com/invoices/'.Str::uuid(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => ['status' => PaymentStatusEnum::PAID, 'paid_at' => now()]);
    }
}
