<?php

namespace App\Enums;

enum SubscriptionStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public static function label(self $status): string
    {
        return match ($status) {
            self::ACTIVE => __('Active'),
            self::EXPIRED => __('Expired'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::ACTIVE => 'success',
            self::EXPIRED => 'danger',
            self::CANCELLED => 'warning',
        };
    }

    public static function options(): array
    {
        return [
            self::ACTIVE->value => self::label(self::ACTIVE),
            self::EXPIRED->value => self::label(self::EXPIRED),
            self::CANCELLED->value => self::label(self::CANCELLED),
        ];
    }
}
