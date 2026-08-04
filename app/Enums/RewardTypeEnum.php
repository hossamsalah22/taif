<?php

namespace App\Enums;

enum RewardTypeEnum: string
{
    case GIF = 'gif';
    case PARTICLE = 'particle';
    case SOUND = 'sound';

    public static function label(self $status): string
    {
        return match ($status) {
            self::GIF => __('GIF Animation'),
            self::PARTICLE => __('Particle Vector'),
            self::SOUND => __('Motivational Sound FX'),
        };
    }

    public static function icon(self $status): string
    {
        return match ($status) {
            self::GIF => 'heroicon-o-rocket-launch',
            self::PARTICLE => 'heroicon-o-sparkles',
            self::SOUND => 'heroicon-o-speaker-wave-2',
        };
    }

    public static function colors(self $status): string
    {
        return match ($status) {
            self::GIF => 'primary',
            self::PARTICLE => 'success',
            self::SOUND => 'warning',
        };
    }

    public static function options(): array
    {
        return [
            self::GIF->value => __('GIF Animation'),
            self::PARTICLE->value => __('Particle Vector'),
            self::SOUND->value => __('Motivational Sound FX'),
        ];
    }
}
