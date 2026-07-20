<?php

namespace App\Enums;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public static function label(self $status): string
    {
        return match ($status) {
            self::MALE => __('male'),
            self::FEMALE => __('female')
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::MALE => 'primary',
            self::FEMALE => 'success'
        };
    }

    public static function options(): array
    {
        return [
            self::MALE->value => __('male'),
            self::FEMALE->value => __('female'),
        ];
    }
}
