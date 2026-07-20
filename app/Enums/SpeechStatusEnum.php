<?php

namespace App\Enums;

enum SpeechStatusEnum: string
{
    case VERBAL = 'verbal';
    case NON_VERBAL = 'non_verbal';

    public static function label(self $status): string
    {
        return match ($status) {
            self::VERBAL => __('Verbal'),
            self::NON_VERBAL => __('Non Verbal'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::VERBAL => 'green',
            self::NON_VERBAL => 'red'
        };
    }

    public static function options(): array
    {
        return [
            self::VERBAL->value => __('Verbal'),
            self::NON_VERBAL->value => __('Non Verbal'),
        ];
    }
}
