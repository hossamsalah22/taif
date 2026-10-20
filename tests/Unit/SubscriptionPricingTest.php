<?php

namespace Tests\Unit;

use App\Services\SubscriptionPricing;
use PHPUnit\Framework\TestCase;

class SubscriptionPricingTest extends TestCase
{
    public function test_tax_is_extracted_from_the_price_and_commission_is_taken_before_tax(): void
    {
        $pricing = SubscriptionPricing::calculateWith(115, 15, 15);

        $this->assertSame(115.0, $pricing['price']);
        $this->assertSame(100.0, $pricing['net_amount']);
        $this->assertSame(15.0, $pricing['tax_amount']);
        $this->assertSame(15.0, $pricing['platform_commission']);
        $this->assertSame(85.0, $pricing['provider_amount']);
    }

    public function test_parts_always_add_up_to_the_price_when_rounding(): void
    {
        $pricing = SubscriptionPricing::calculateWith(99.99, 15, 12.5);

        $this->assertSame(86.95, $pricing['net_amount']);
        $this->assertSame(13.04, $pricing['tax_amount']);
        $this->assertSame(99.99, round($pricing['net_amount'] + $pricing['tax_amount'], 2));
        $this->assertSame($pricing['net_amount'], round($pricing['platform_commission'] + $pricing['provider_amount'], 2));
    }

    public function test_free_package_has_no_tax_and_no_commission(): void
    {
        $pricing = SubscriptionPricing::calculateWith(0, 15, 15);

        $this->assertSame(0.0, $pricing['tax_amount']);
        $this->assertSame(0.0, $pricing['platform_commission']);
        $this->assertSame(0.0, $pricing['provider_amount']);
    }

    public function test_zero_rates_leave_the_whole_price_to_the_provider(): void
    {
        $pricing = SubscriptionPricing::calculateWith(200, 0, 0);

        $this->assertSame(200.0, $pricing['net_amount']);
        $this->assertSame(0.0, $pricing['tax_amount']);
        $this->assertSame(200.0, $pricing['provider_amount']);
    }

    public function test_out_of_range_values_are_clamped(): void
    {
        $pricing = SubscriptionPricing::calculateWith(-50, -5, 150);

        $this->assertSame(0.0, $pricing['price']);
        $this->assertSame(0.0, $pricing['tax_rate']);
        $this->assertSame(100.0, $pricing['commission_rate']);
    }
}
