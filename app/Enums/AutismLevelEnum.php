<?php

namespace App\Enums;

enum AutismLevelEnum: string
{
    case SEVERE = 'severe';
    case MODERATE = 'moderate';
    case MILD = 'mild';

    public static function label(self $status): string
    {
        return match ($status) {
            self::SEVERE => __('Severe'),
            self::MODERATE => __('Moderate'),
            self::MILD => __('Mild'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::SEVERE => 'red',
            self::MODERATE => 'yellow',
            self::MILD => 'green',
        };
    }

    public static function options(): array
    {
        return [
            self::SEVERE->value => __('Severe'),
            self::MODERATE->value => __('Moderate'),
            self::MILD->value => __('Mild'),
        ];
    }
}
