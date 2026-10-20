<?php

namespace App\Services;

use App\Settings\GeneralSettings;

/**
 * Price breakdown of a subscription package. The package price already INCLUDES the tax:
 *
 *   price (what the parent pays) = net + tax
 *   net (before tax)             = price / (1 + tax rate)
 *   tax                          = price - net
 *   platform commission          = net * commission rate
 *   service provider share       = net - platform commission
 *
 * e.g. 115 SAR, tax 15%, commission 15%:  net 100 + tax 15, platform 15, provider 85.
 */
class SubscriptionPricing
{
    /**
     * @return array{price: float, tax_rate: float, commission_rate: float, net_amount: float, tax_amount: float, platform_commission: float, provider_amount: float}
     */
    public static function calculate(float $price, ?GeneralSettings $settings = null): array
    {
        $settings ??= app(GeneralSettings::class);

        return self::calculateWith($price, (float) $settings->tax, (float) $settings->commission);
    }

    /**
     * Same breakdown from explicit values (the settings screen previews it live while typing).
     *
     * @return array{price: float, tax_rate: float, commission_rate: float, net_amount: float, tax_amount: float, platform_commission: float, provider_amount: float}
     */
    public static function calculateWith(float $price, float $taxRate, float $commissionRate): array
    {
        $price = round(max(0.0, $price), 2);
        $taxRate = min(max(0.0, $taxRate), 100.0);
        $commissionRate = min(max(0.0, $commissionRate), 100.0);

        $net = round($price / (1 + $taxRate / 100), 2);
        $commission = round($net * $commissionRate / 100, 2);

        return [
            'price' => $price,
            'tax_rate' => $taxRate,
            'commission_rate' => $commissionRate,
            'net_amount' => $net,
            'tax_amount' => round($price - $net, 2), // net + tax always add up to the price
            'platform_commission' => $commission,
            'provider_amount' => round($net - $commission, 2),
        ];
    }
}
