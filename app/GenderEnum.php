<?php

namespace App;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MALE => __('male'),
            self::FEMALE => __('female'),
            self::OTHER => __('other'),
        };
    }
}
