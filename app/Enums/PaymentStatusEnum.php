<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';

    public static function label(self $status): string
    {
        return match ($status) {
            self::PENDING => __('Pending'),
            self::PAID => __('Paid'),
            self::FAILED => __('Failed'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
        };
    }

    public static function options(): array
    {
        return [
            self::PENDING->value => self::label(self::PENDING),
            self::PAID->value => self::label(self::PAID),
            self::FAILED->value => self::label(self::FAILED),
        ];
    }
}
