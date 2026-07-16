<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public int $plan_grace_period_days;

    public ?string $phone;

    public ?string $whatsapp;

    public ?string $email;

    public ?string $app_store_link;

    public ?string $play_store_link;

    public float $tax;

    public float $commission;

    public ?string $facebook;

    public ?string $instagram;

    public ?string $snapchat;

    public ?string $linkedin;

    public ?string $tiktok;

    /**
     * Tax type is always percentage — fixed amount is not supported.
     * Stored value is ignored; this always returns 'percentage'.
     */
    public function getTaxType(): string
    {
        return 'percentage';
    }

    /**
     * Commission type is always percentage — fixed amount is not supported.
     * Stored value is ignored; this always returns 'percentage'.
     */
    public function getCommissionType(): string
    {
        return 'percentage';
    }

    public static function group(): string
    {
        return 'general';
    }
}
