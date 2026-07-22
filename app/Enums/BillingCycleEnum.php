<?php

namespace App\Enums;

enum BillingCycleEnum: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnually = 'semi-annually';
    case Annually = 'annually';

    public static function label(self $status): string
    {
        return match ($status) {
            self::Monthly => __('Monthly'),
            self::Quarterly => __('Quarterly'),
            self::SemiAnnually => __('Semi Annually'),
            self::Annually => __('Annually'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::Monthly => 'red',
            self::Quarterly => 'yellow',
            self::SemiAnnually => 'green',
            self::Annually => 'blue',
        };
    }

    public static function options(): array
    {
        return [
            self::Monthly->value => __('Monthly'),
            self::Quarterly->value => __('Quarterly'),
            self::SemiAnnually->value => __('Semi Annually'),
            self::Annually->value => __('Annually'),
        ];
    }
}
