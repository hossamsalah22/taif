<?php

namespace App\Enums;

enum DifficultyLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public static function label(self $status): string
    {
        return match ($status) {
            self::LOW => __('Low'),
            self::MEDIUM => __('Medium'),
            self::HIGH => __('High'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::LOW => 'primary',
            self::MEDIUM => 'success',
            self::HIGH => 'danger',
        };
    }

    public static function options(): array
    {
        return [
            self::LOW->value => __('Low'),
            self::MEDIUM->value => __('Medium'),
            self::HIGH->value => __('High'),
        ];
    }
}
